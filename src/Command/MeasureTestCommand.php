<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Command;

use App\Model\GitRepoUrl;
use App\Service\CheckoutGitRepository;
use App\Service\MeasureTestGitRepository;
use App\Util\FilterSemver;
use League\Csv\Writer;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MeasureTestCommand extends Command
{
    /**
     * @var CheckoutGitRepository
     */
    private $git;

    /**
     * @var MeasureTestGitRepository
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
     * @param MeasureTestGitRepository $metrics
     * @param PheanstalkInterface $queue
     * @param string $defaultCSVDirectory
     */
    public function __construct(CheckoutGitRepository $git, MeasureTestGitRepository $metrics, PheanstalkInterface $queue, string $defaultCSVDirectory)
    {
        parent::__construct();
        $this->git = $git;
        $this->metrics = $metrics;
        $this->queue = $queue;
        $this->defaultCSVDirectory = $defaultCSVDirectory;
    }


    public function configure()
    {
        $this->setName('app:measure-test')->setDescription('Measures all the things');
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
            ->watch('measure-test')
            ->ignore('default')
            ->reserve();
        $jobData = json_decode($job->getData(), true);

        $gitRepoUrl = new GitRepoUrl($jobData['name'], $jobData['repo']);

        $filePath = sprintf(
            '%s/measurement-data/%s.csv',
            $this->defaultCSVDirectory,
            str_replace('/', '-', $gitRepoUrl->getName())
        );

        if (file_exists($filePath)) {
            //already measured
            //
            $this->queue->delete($job);
            $output->writeln(sprintf('Already measured, end %s %s', date('H:i:s'), $gitRepoUrl->getName()));

            return true;
        }

        if (!file_exists($filePath)) {
            touch($filePath);
        }
        $csv = Writer::createFromPath($filePath);

        $csv->insertOne(
            [
                'repository',
                'tag',
                'coverage',
            ]
        );

        $output->writeln(sprintf('Start %s %s', date('H:i:s'), $gitRepoUrl->getName()));

        try {
            $repository = $this->git->checkoutRepo($gitRepoUrl->getUrl());
            $tags = FilterSemver::getMinors($this->git->getAllTags($repository));
            $results = $this->metrics->withTags($gitRepoUrl->getName(), $repository, $tags);

            $csv->insertAll($results);
        } catch (\Exception $exception) {
            $this->git->removeRepo($repository);
            $output->writeln($exception->getMessage());
            $output->writeln(sprintf('End with error %s %s', date('H:i:s'), $gitRepoUrl->getName()));
            $this->queue->bury($job);
        }

        $this->git->removeRepo($repository);

        $output->writeln(sprintf('End %s %s', date('H:i:s'), $gitRepoUrl->getName()));

        $this->queue->delete($job);

        return true;
    }
}
