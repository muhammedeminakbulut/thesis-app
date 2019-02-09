<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Service;

class Percentiles
{
    const ONE_STAR = 0.05;
    const TWO_STAR = 0.35;
    const THREE_STAR = 0.65;
    const FOUR_STAR = 0.95;

    const PERCENTILES = [
        self::ONE_STAR,
        self::TWO_STAR,
        self::THREE_STAR,
        self::FOUR_STAR,
    ];

    /**
     * @param array $dataset
     * @return array
     */
    public static function calculatePercentiles(array $dataset): array
    {
        $percentiles = [];
        foreach (self::PERCENTILES as $percentile) {
            $percentiles[(string)$percentile] = Percentiles::calculatePercentile($dataset, $percentile);
        }

        return $percentiles;
    }

    /**
     * @param $data
     * @param $percentile
     * @return float|int
     * @see https://stackoverflow.com/questions/24048879/how-can-i-calculate-the-nth-percentile-from-an-array-of-doubles-in-php
     */
    public static function calculatePercentile($data, $percentile)
    {
        $index = $percentile * count($data);
        if (floor($index) == $index) {
            return ($data[$index - 1] + $data[$index]) / 2;
        }

        return $data[(int)floor($index)];
    }

    public static function classifyScoreDesc($value, $percentiles)
    {
        $value = (float)$value;
        switch ($value) {
            case $value < $percentiles[(string)self::FOUR_STAR]:
                return 5;
            case $value < $percentiles[(string)self::THREE_STAR] && $value >= $percentiles[(string)self::FOUR_STAR]:
                return 4;
            case $value < $percentiles[(string)self::TWO_STAR] && $value >= $percentiles[(string)self::THREE_STAR]:
                return 3;
            case $value < $percentiles[(string)self::ONE_STAR] && $value >= $percentiles[(string)self::TWO_STAR]:
                return 2;
            case $value >= $percentiles[(string)self::ONE_STAR]:
                return 1;
        }

        return 1;
    }

    public static function classifyScoreAsc($value, $percentiles)
    {
        $value = (float)$value;
        switch ($value) {
            case $value <= $percentiles[(string)self::ONE_STAR]:
                return 1;
            case $value > $percentiles[(string)self::ONE_STAR] && $value <= $percentiles[(string)self::TWO_STAR]:
                return 2;
            case $value > $percentiles[(string)self::TWO_STAR] && $value <= $percentiles[(string)self::THREE_STAR]:
                return 3;
            case $value > $percentiles[(string)self::THREE_STAR] && $value <= $percentiles[(string)self::FOUR_STAR]:
                return 4;
            case $value > $percentiles[(string)self::FOUR_STAR]:
                return 5;
        }

        return 1;
    }
}