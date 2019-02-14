<?php
/**
 * Copyright (c) Muhammed Akbulut
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
            if (preg_match('~^v?(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$~', $version)) {
                $newVersions[] = $version;
            }
        }

        return $newVersions;
    }
}
