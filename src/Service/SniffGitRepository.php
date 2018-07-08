<?php

namespace App\Service;

use App\Model\RepositoryInterface;
use TQ\Vcs\Cli\Call;

/**
 * Class SniffGitRepository
 */
class SniffGitRepository
{
    private $standard;

    /**
     * SniffGitRepository constructor.
     * @param string $sniffStandard
     */
    public function __construct(string $sniffStandard = 'PSR2')
    {
        $this->standard = $sniffStandard;
    }

    public function sniff(RepositoryInterface $repo) : array
    {
        $result = Call::create(
            sprintf(
                './vendor/bin/phpcs %s --standard=%s --report=json',
                $repo->getLocalPath(),
                $this->standard
            )
        )->execute();

        $report = json_decode($result->getStdOut(), true);

        if (isset($report['totals'])) {
            return $report['totals'];
        }

        return [
            'errors' => 0,
            'warnings' => 0,
            'fixables' => 0,
        ];
    }
}
