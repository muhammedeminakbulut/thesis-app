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

    public function withTags(string $name, RepositoryInterface $repo, array $tags): array
    {
        $results = [];
        foreach ($tags as $tag) {
            Call::create(sprintf('git checkout %s', $tag), $repo->getLocalPath())->execute();

            if (is_file($repo->getLocalPath().'/composer.lock')) {
                unlink($repo->getLocalPath().'/composer.lock');
            }

            $analysisResult = $this->testCoverage->getCoverage($name, $tag, $repo);
            $results[$tag] = array_merge(
                [$repo->getRemote(), $tag],
                [$analysisResult]
            );
        }

        return $results;
    }
}
