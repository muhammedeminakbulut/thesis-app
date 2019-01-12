<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Command;

use App\Service\GitRepositoryFeed;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use Symfony\Component\Console\Command\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class KickJobsCommand extends Command
{
    /**
     * @var Pheanstalk
     */
    private $queue;

    /**
     * CreateJobsCommand constructor.
     * @param Pheanstalk $queue
     */
    public function __construct(Pheanstalk $queue)
    {
        parent::__construct();
        $this->queue = $queue;
    }

    public function configure()
    {
        $this->setName('app:kick-jobs')->setDescription('kicks jobs');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->queue->useTube('measure')->kick(PHP_INT_MAX);

        return true;
    }
}