<?php

namespace App\Service;

use App\Model\InvalidRepository;
use App\Model\RepositoryInterface;
use SebastianBergmann\FinderFacade\FinderFacade;
use SebastianBergmann\PHPLOC\Analyser;

class AnalyseGitRepository
{
    public function analyse(RepositoryInterface $repo) : array
    {
        if ($repo instanceof InvalidRepository) {
            return [];
        }

        try {
            $finder = new FinderFacade([$repo->getLocalPath()]);
            $files  = $finder->findFiles();
        } catch (\InvalidArgumentException $exception) {
            return [];
        }

        if (empty($files)) {
            return [];
        }

        $analyser = new Analyser();

        return $analyser->countFiles($files, null);
    }
}
