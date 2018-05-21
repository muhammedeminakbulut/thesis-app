<?php
/**
 * Created by PhpStorm.
 * User: muhammed
 * Date: 20-05-18
 * Time: 11:51
 */

namespace App\Service;


use SebastianBergmann\FinderFacade\FinderFacade;
use SebastianBergmann\PHPLOC\Analyser;
use TQ\Vcs\Cli\Call;

class MeasureGitRepository
{
    public function withTags($repo, array $tags) : array
    {
        $results = [];
        foreach ($tags as $tag)
        {
            $callResult = Call::create(sprintf('git checkout %s', $tag), $repo)->execute();

            try {
                $finder = new FinderFacade([$repo]);
                $files  = $finder->findFiles();
            } catch (\InvalidArgumentException $ex) {
                return [];
            }

            if (empty($files)) {
                return [];
            }

            $analyser = new Analyser();

            $results[$tag] = $analyser->countFiles($files, null);
        }

        return $results;
    }
}