<?php
/**
 * Created by PhpStorm.
 * User: muhammed
 * Date: 15-05-18
 * Time: 21:50
 */

namespace App\Util;


class CleanupSemver
{
    /**
     * currently only first 3 of semver. major, minor, bugfix
     *
     * @param array $versions
     * @return array
     */
    public static function cleanUp(array $versions): array
    {
        $newVersions = [];

        foreach ($versions as $version) {
            if (preg_match('~^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$~', $version)) {
                $newVersions[] = $version;
            }
        }
        
        return $newVersions;
    }
}