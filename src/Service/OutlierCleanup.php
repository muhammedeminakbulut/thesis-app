<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Service;


class OutlierCleanup
{
    /**
     * @param $dataset
     * @param int $magnitude
     * @return array
     *
     * @see https://stackoverflow.com/questions/15174952/finding-and-removing-outliers-in-php
     */
    public static function removeOutliers($dataset, $magnitude = 1): array
    {
        $count = count($dataset);
        // Calculate the mean
        $mean = array_sum($dataset) / $count;
        // Calculate standard deviation and times by magnitude
        $deviation = sqrt(array_sum(array_map('App\Service\OutlierCleanup::sdSquare', $dataset, array_fill(0, $count, $mean))) / $count) * $magnitude;
        // Return filtered array of values that lie within $mean +- $deviation.
        return array_filter(
            $dataset,
            function ($x) use ($mean, $deviation) {
                return ($x <= $mean + $deviation && $x >= $mean - $deviation);
            }
        );
    }

    public static function sdSquare($x, $mean)
    {
        return pow($x - $mean, 2);
    }
}