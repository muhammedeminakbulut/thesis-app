<?php
namespace App\Service;


use App\Util\CleanupSemver;
use Composer\Semver\Semver;
use TQ\Git\Repository\Repository;
use TQ\Git\StreamWrapper\StreamWrapper;
use TQ\Vcs\Cli\Call;

class CheckoutGitRepository
{
    const CHECKOUT_DIR = '/Users/muhammed/Documents/studie/Afstuderen/app';

    public function checkoutRepo($gitUrl)
    {
        // tmp name to remove afterwards
        $repo = strtolower(md5(date('His')));

        $callResult = Call::create(sprintf('git clone %s %s', $gitUrl, $repo), self::CHECKOUT_DIR)->execute();

        if ($callResult->getReturnCode() === 0) {
            return sprintf('%s/%s', self::CHECKOUT_DIR, $repo);
        }

        // maak een data object
        return false;
    }

    public function getAllTags($repoUrl): array
    {
        StreamWrapper::register('git');
        $git = Repository::open($repoUrl);

        $tags = explode(PHP_EOL, $git->getGit()->{'tag'}($repoUrl)->getStdOut());
        return Semver::sort(CleanupSemver::cleanUp($tags));
    }

    public function removeRepo($repoUri)
    {

        $callResult = Call::create(sprintf('rm -rf %s', $repoUri), self::CHECKOUT_DIR)->execute();
    }
}