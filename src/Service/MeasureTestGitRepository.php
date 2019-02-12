<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Service;

use App\Model\RepositoryInterface;
use TQ\Vcs\Cli\Call;

class MeasureTestGitRepository
{

    /**
     * @var TestCoverageGitRepository
     */
    private $testCoverage;

    /**
     * MeasureTestGitRepository constructor.
     * @param TestCoverageGitRepository $testCoverage
     */
    public function __construct(TestCoverageGitRepository $testCoverage)
    {
        $this->testCoverage = $testCoverage;
    }

    public function withTags(RepositoryInterface $repo, array $tags): array
    {
        $results = [];
        foreach ($tags as $tag) {
            Call::create(sprintf('git checkout %s', $tag), $repo->getLocalPath())->execute();

            if (is_dir($repo->getLocalPath().'/vendor')) {
                Call::create('rm -rf vendor/', $repo->getLocalPath())->execute();
            }

            $analysisResult = $this->testCoverage->getCoverage($repo);
            $results[$tag] = array_merge(
                [$repo->getRemote(), $tag],
                [$analysisResult]
            );
        }

        return $results;
    }
}
