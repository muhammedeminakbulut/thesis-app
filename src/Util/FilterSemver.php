<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Util;

/**
 * Utility to filter out all the small bugfix versions. To speedup measuring.
 * @package App\Util
 */
class FilterSemver
{
    static function getMinors($tags): array
    {
        $versions = [];
        foreach ($tags as $tag) {
            $versions[self::getMajor($tag)][self::getMinor($tag)][self::getBugfix($tag)] = $tag;
        }

        $filtered = [];
        foreach ($versions as $major) {
            foreach ($major as $minor) {
                $filtered[] = current($minor);
            }
        }

        return $filtered;
    }

    static function getMajor($tag): int
    {
        $explodees = explode('.', $tag);

        return (int) $explodees[0];
    }

    static function getMinor($tag): int
    {
        $explodees = explode('.', $tag);

        return (int) $explodees[1];
    }

    static function getBugfix($tag): int
    {
        $explodees = explode('.', $tag);

        return (int) $explodees[2];
    }
}
