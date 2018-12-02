<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Service;

use App\Model\InvalidRepository;
use App\Model\RepositoryInterface;
use SebastianBergmann\FinderFacade\FinderFacade;
use SebastianBergmann\PHPCPD\Detector\Detector;
use SebastianBergmann\PHPCPD\Detector\Strategy\DefaultStrategy;
use SebastianBergmann\PHPLOC\Analyser;

class AnalyseGitRepository
{
    public function analyse(RepositoryInterface $repo) : array
    {
        if ($repo instanceof InvalidRepository) {
            return [];
        }

        try {
            $finder = new FinderFacade([$repo->getLocalPath()], [], ['*.php']);
            $files  = $finder->findFiles();
        } catch (\InvalidArgumentException $exception) {
            return [];
        }

        if (empty($files)) {
            return [];
        }

        $analyser = new Analyser();

        $result = $analyser->countFiles($files, null);

        /**
         * part of the copy paste detector.
         */
        $strategy = new DefaultStrategy();
        $detector = new Detector($strategy);

        $clones = $detector->copyPasteDetection($files);

        return [
            'loc' => $result['loc'],
            'cloc' => $result['cloc'],
            'cyclomatic_complexity' => $result['ccn'],
            'duplication' => $clones->getPercentage(),
            'unit_size' => $result['methodLlocAvg'],
            'unit_interface-size' => 0,
        ];
    }
}
