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

    public function withTags(RepositoryInterface $repo, array $tags): array
    {
        $results = [];
        foreach ($tags as $tag) {
            Call::create(sprintf('git checkout %s', $tag), $repo->getLocalPath())->execute();

            $analysisResult = $this->analyser->analyse($repo);
            if (count($analysisResult) === 0) {
                continue;
            }
            $results[$tag] = array_merge(
                [$repo->getRemote(), $tag],
                $this->prependKey('analyser_', $analysisResult),
                $this->prependKey('sniffer_', $this->sniffer->sniff($repo))
            );
        }

        return $results;
    }

    private function prependKey(string $pre, array $data): array
    {
        $newArray = [];
        foreach ($data as $key => $value) {
            $newArray[$pre . $key] = $value;
        }
        return $newArray;
    }
}
