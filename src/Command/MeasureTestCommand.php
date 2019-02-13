<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Command;

use App\Model\GitRepoUrl;
use App\Service\CheckoutGitRepository;
use App\Service\MeasureGitRepository;
use App\Service\MeasureTestGitRepository;
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
//
//        $job = $this->queue
//            ->watch('measure')
//            ->ignore('default')
//            ->reserve();
//        $jobData = json_decode($job->getData(), true);



        $jobData = [
            'name' => 'thephpleague/flysystem',
            'repo' => 'git@github.com:thephpleague/flysystem.git',
            'tags' => [
                '1.0.0',
            ]
        ];
        $gitRepoUrl = new GitRepoUrl($jobData['name'], $jobData['repo']);

        $filePath = sprintf(
            '%s/measurement-data/%s.csv',
            $this->defaultCSVDirectory,
            str_replace('/', '-', $gitRepoUrl->getName())
        );

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
        $tags = $jobData['tags'];
        try {
            $repository = $this->git->checkoutRepo($gitRepoUrl->getUrl());

            $results = $this->metrics->withTags($gitRepoUrl->getName(), $repository, $tags);

            $csv->insertAll($results);
        } catch (\Exception $exception) {
            $this->git->removeRepo($repository);
            $output->writeln(sprintf('End with error %s %s', date('H:i:s'), $gitRepoUrl->getName()));
            //$this->queue->bury($job);
        }

        $this->git->removeRepo($repository);

        $output->writeln(sprintf('End %s %s', date('H:i:s'), $gitRepoUrl->getName()));

        //$this->queue->delete($job);

        return true;
    }
}
