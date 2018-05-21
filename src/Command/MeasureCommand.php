<?php

namespace App\Command;

use App\Service\CheckoutGitRepository;
use App\Service\MeasureGitRepository;
use SebastianBergmann\FinderFacade\FinderFacade;
use SebastianBergmann\PHPLOC\Analyser;
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
     * MeasureCommand constructor.
     * @param CheckoutGitRepository $git
     * @param MeasureGitRepository $metrics
     */
    public function __construct(CheckoutGitRepository $git, MeasureGitRepository $metrics)
    {
        parent::__construct();
        $this->git = $git;
        $this->metrics = $metrics;

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
        $repo = '/Users/muhammed/Documents/studie/Afstuderen/app/url-bundle';

        $output->writeln([
            'Measure all the things',
            '============',
            '',
        ]);

        $output->writeln('Start '. date('H:i:s'));
        $repoUri = $this->git->checkoutRepo('git@github.com:zicht/url-bundle.git');

        $tags = $this->git->getAllTags($repoUri);

        $results = $this->metrics->withTags($repoUri, $tags);

        $this->git->removeRepo($repoUri);

        $output->writeln('End '. date('H:i:s'));
    }
}