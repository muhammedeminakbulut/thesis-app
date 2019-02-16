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

    public function getCoverage(string $name, string $tag, RepositoryInterface $repo): string
    {
        if (!is_file(sprintf('%s/composer.json', $repo->getLocalPath()))) {
            return 'no composer';
        }

        $result = Call::create(
            'composer install -n -q --no-scripts --no-progress --no-suggest',
            $repo->getLocalPath()
        )->execute();

        if ($result->getReturnCode() !== 0) {
            return 'composer failed, ' . $result->getStdErr();
        }

        if (!(is_file(sprintf('%s/phpunit.xml.dist', $repo->getLocalPath())) || is_file(sprintf('%s/phpunit.xml', $repo->getLocalPath())))) {
            return 'no phpunit config';
        }

        $phpunitPath = $this->findPHPUNIT($repo->getLocalPath());

        if ($phpunitPath === null) {
            return 'phpunit not installed';
        }

        $filePath = sprintf('%s/%s_%s.txt',
            $this->cwd . '/data/coverage',
            str_replace('/', '-', $name),
            $tag
        );

        $result = Call::create(
            sprintf(
                '%s --coverage-text=%s',
                $phpunitPath,
                $filePath
            ),
            $repo->getLocalPath()
        )->execute();

        if ($result->getReturnCode() !== 0) {
            return 'phpunit failed';
        }

        $report = file_get_contents($filePath);

        $lines = explode(PHP_EOL, $report);
        $matches = [];
        $regexp = '/Lines:\s*(\d*.\d*)%\s/';
        preg_match($regexp, $lines[8], $matches);

        return isset($matches[1]) ? $matches[1] : '0.00';
    }


    private function findPHPUNIT($path): ?string
    {
        $paths = [
            'vendor/phpunit/phpunit/phpunit',
            'vendor/bin/phpunit',
            'phpunit',
        ];

        foreach ($paths as $value) {
            if (is_file(sprintf('%s/%s', $path, $value))) {
                return $value;
            }
        }

        return null;
    }
}
