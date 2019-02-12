<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Service;

use App\Model\RepositoryInterface;
use TQ\Vcs\Cli\Call;

/**
 * Class TestCoverageGitRepository
 */
class TestCoverageGitRepository
{
    /**
     * @var string
     */
    private $cwd;

    /**
     * @param string $cwd
     */
    public function __construct(string $cwd)
    {
        $this->cwd = $cwd;
    }

    public function getCoverage(RepositoryInterface $repo) : string
    {
        $result = Call::create(
            'composer install --dev',
            $repo->getLocalPath()
        )->execute();

        if ($result->getReturnCode() !== 0) {
           return 'composer failed, '.$result->getStdErr();
        }

        if (is_file(sprintf('%s/phpunit.xml.dist', $repo->getLocalPath())) || is_file(sprintf('%s/phpunit.xml', $repo->getLocalPath()))) {
            return 'no phpunit config';
        }

        $result = Call::create(
            sprintf(
                '%s/vendor/bin/phpunit --coverage-text',
                $repo->getLocalPath()
            ),
            $this->cwd
        )->execute();

        $report = $result->getStdOut();

        var_dump($report);die;
    }
}
