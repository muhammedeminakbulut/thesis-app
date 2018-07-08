<?php

namespace App\Service;

use App\Model\RepositoryInterface;
use TQ\Vcs\Cli\Call;

class MeasureGitRepository
{
    /**
     * @var AnalyseGitRepository
     */
    private $analyser;

    /**
     * @var SniffGitRepository
     */
    private $sniffer;

    /**
     * MeasureGitRepository constructor.
     * @param AnalyseGitRepository $analyser
     * @param SniffGitRepository $sniffer
     */
    public function __construct(AnalyseGitRepository $analyser, SniffGitRepository $sniffer)
    {
        $this->analyser = $analyser;
        $this->sniffer = $sniffer;
    }

    public function withTags(RepositoryInterface $repo, array $tags) : array
    {
        $results = [];
        foreach ($tags as $tag) {
            Call::create(sprintf('git checkout %s', $tag), $repo->getLocalPath())->execute();

            $results[$tag]['analyser'] = $this->analyser->analyse($repo);
            $results[$tag]['sniffer'] = $this->sniffer->sniff($repo);
        }

        return $results;
    }
}
