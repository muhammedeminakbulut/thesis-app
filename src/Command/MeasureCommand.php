<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Command;

use App\Model\GitRepoUrl;
use App\Service\CheckoutGitRepository;
use App\Service\MeasureGitRepository;
use League\Csv\Writer;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MeasureCommand extends Command
{
    /**
     * @var CheckoutGitRepository
     */
    private $git;

    /**
     * @var MeasureGitRepository
     */
    private $metrics;

    /**
     * @var Pheanstalk
     */
    private $queue;

    /**
     * @var string
     */
    private $defaultCSVDirectory;

    /**
     * MeasureCommand constructor.
     * @param CheckoutGitRepository $git
     * @param MeasureGitRepository $metrics
     * @param Pheanstalk $queue
     * @param string $defaultCSVDirectory
     */
    public function __construct(CheckoutGitRepository $git, MeasureGitRepository $metrics, Pheanstalk $queue, string $defaultCSVDirectory)
    {
        parent::__construct();
        $this->git = $git;
        $this->metrics = $metrics;
        $this->queue = $queue;
        $this->defaultCSVDirectory = $defaultCSVDirectory;
    }


    public function configure()
    {
        $this->setName('app:measure')->setDescription('Measures all the things');
    }

    /**
     * The plan:
     * Checkout library
     * Get all tags
     * measure per tag
     * remove lib.
     * repeat next lib
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Measure all the things',
            '============',
            '',
        ]);

        $job = $this->queue
            ->watch('measure')
            ->ignore('default')
            ->reserve();
        $jobData = json_decode($job->getData(), true);
        $gitRepoUrl = new GitRepoUrl($jobData['name'], $jobData['repo']);

        $filePath = sprintf(
            '%s/measurement-data/%s.csv',
            $this->defaultCSVDirectory,
            str_replace('/', '-',$gitRepoUrl->getName())
        );

        if (!file_exists($filePath)) {
            touch($filePath);
        }
        $csv = Writer::createFromPath($filePath);

        $csv->insertOne(
            [
                'repository',
                'tag',
                'analyser_loc',
                'analyser_cloc',
                'analyser_cyclomatic_complexity',
                'analyser_duplication',
                'analyser_unit_size',
                'analyser_unit_interface_size',
                'sniffer_errors',
                'sniffer_warnings',
            ]
        );

        $output->writeln(sprintf('Start %s %s', date('H:i:s'), $gitRepoUrl->getName()));

        $repository = $this->git->checkoutRepo($gitRepoUrl->getUrl());

        $tags = $this->git->getAllTags($repository);

        $results = $this->metrics->withTags($repository, $tags);

        $csv->insertAll($results);

        $this->git->removeRepo($repository);

        $output->writeln(sprintf('End %s %s', date('H:i:s'), $gitRepoUrl->getName()));

        $this->queue->delete($job);

        return true;
    }
}
