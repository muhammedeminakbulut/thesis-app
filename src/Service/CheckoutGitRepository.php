<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Service;

use App\Model\GitRepository;
use App\Model\InvalidRepository;
use App\Model\RepositoryInterface;
use App\Util\CleanupSemver;
use Composer\Semver\Semver;
use TQ\Git\Repository\Repository;
use TQ\Git\StreamWrapper\StreamWrapper;
use TQ\Vcs\Cli\Call;

class CheckoutGitRepository
{
    /**
     * @var string
     */
    private $checkoutDir;

    /**
     * CheckoutGitRepository constructor.
     * @param string $checkoutDir
     */
    public function __construct(string $checkoutDir)
    {
        $this->checkoutDir = $checkoutDir;
        StreamWrapper::register('git');
    }

    public function checkoutRepo($gitUrl): RepositoryInterface
    {
        // tmp name to remove afterwards
        $repo = strtolower(md5(date('His') . rand(100, PHP_INT_MAX)));

        $callResult = Call::create(sprintf('git clone %s %s', $gitUrl, $repo), $this->checkoutDir)->execute();

        if ($callResult->getReturnCode() === 0) {
            return new GitRepository($gitUrl, sprintf('%s/%s', $this->checkoutDir, $repo));
        }

        return new InvalidRepository($gitUrl);
    }

    public function getAllTags(RepositoryInterface $repository): array
    {
        $git = Repository::open($repository->getLocalPath());

        $tags = explode(PHP_EOL, $git->getGit()->{'tag'}($repository->getLocalPath())->getStdOut());
        return Semver::sort(CleanupSemver::cleanUp($tags));
    }

    public function removeRepo(RepositoryInterface $repository): void
    {
        if ($repository instanceof InvalidRepository) {
            return;
        }

        Call::create(sprintf('rm -rf %s', $repository->getLocalPath()), $this->checkoutDir)->execute();
    }
}
