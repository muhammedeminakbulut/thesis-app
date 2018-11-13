<?php
/**
 * Created by PhpStorm.
 * User: muhammed
 * Date: 13-11-18
 * Time: 21:50
 */

namespace App\Command;


use App\Service\GitRepositoryFeed;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateJobsCommand extends Command
{
    /**
     * @var GitRepositoryFeed
     */
    private $repoFeed;

    /**
     * @var Pheanstalk
     */
    private $queue;

    /**
     * CreateJobsCommand constructor.
     * @param GitRepositoryFeed $repoFeed
     * @param Pheanstalk $queue
     */
    public function __construct(GitRepositoryFeed $repoFeed, Pheanstalk $queue)
    {
        parent::__construct();
        $this->repoFeed = $repoFeed;
        $this->queue = $queue;
    }

    public function configure()
    {
        $this->setName('app:create-jobs')->setDescription('creates jobs');
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
        foreach ($this->repoFeed->getRepositories() as $gitRepoUrl) {
            $this->queue->useTube('measure')->put(json_encode(['repo' => $gitRepoUrl->getUrl(), 'name' => $gitRepoUrl->getName()]));
        }

        return true;
    }
}