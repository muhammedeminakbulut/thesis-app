<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Service;

use App\Model\RepositoryInterface;
use TQ\Vcs\Cli\Call;

class CountContributors
{
    /**
     * @param RepositoryInterface $repository
     * @return int
     */
    public static function countContributors(RepositoryInterface $repository): int
    {
        $repository->getLocalPath();

        $result = Call::create('git log --all --format=\'%aN\' | sort -u | wc -l', $repository->getLocalPath())->execute();

        return $result->getReturnCode() === 0 ? (int)$result->getStdOut() : 0;
    }
}