<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Command;

use League\Csv\Reader;
use League\Csv\Statement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClassifyScoresCommand extends Command
{
    const PERCENTILES = [
        0.05,
        0.35,
        0.65,
        0.95,
    ];

    public function configure()
    {
        $this->setName('app:classify')->setDescription('score error data');
        $this->setDefinition(
            new InputDefinition(
                [
                    new InputArgument('from-file', InputArgument::REQUIRED),
                    new InputArgument('column', InputArgument::REQUIRED),
                    new InputArgument('sort-order', InputArgument::REQUIRED),
                ]
            )
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('from-file');
        $column = $input->getArgument('column');
        $sortOrder = $input->getArgument('sort-order');

        $reader = Reader::createFromPath($filePath);
        $reader->setHeaderOffset(0);

        $records = (new Statement())->process($reader);
        $columnData = [];
        foreach ($records->fetchColumn($column) as $value) {
            if (empty($value)) {
                continue;
            }

            if ((float)$value === (float)0) {
                continue;
            }

            $columnData[] = (float)$value;
        }

        if ($sortOrder === 'asc') {
            sort($columnData);
        } elseif ($sortOrder === 'desc') {
            rsort($columnData);
        }

        $data = $this->removeOutliers($columnData);

        if ($sortOrder === 'asc') {
            sort($data);
        } elseif ($sortOrder === 'desc') {
            rsort($data);
        }

        $percentiles = $this->calculatePercentiles($data);

        foreach ($percentiles as $key => $value) {
            $output->writeln(sprintf('percentile %s: %s', $key, $value));
        }

        return true;
    }

    /**
     * @param $dataset
     * @param int $magnitude
     * @return array
     *
     * @see https://stackoverflow.com/questions/15174952/finding-and-removing-outliers-in-php
     */
    private function removeOutliers($dataset, $magnitude = 1): array
    {
        $count = count($dataset);
        // Calculate the mean
        $mean = array_sum($dataset) / $count;
        // Calculate standard deviation and times by magnitude
        $deviation = sqrt(array_sum(array_map([$this, 'sdSquare'], $dataset, array_fill(0, $count, $mean))) / $count) * $magnitude;
        // Return filtered array of values that lie within $mean +- $deviation.
        return array_filter(
            $dataset,
            function ($x) use ($mean, $deviation) {
                return ($x <= $mean + $deviation && $x >= $mean - $deviation);
            }
        );
    }

    static public function sdSquare($x, $mean)
    {
        return pow($x - $mean, 2);
    }

    private function calculatePercentiles($dataset)
    {
        $percentiles = [];
        foreach (self::PERCENTILES as $percentile) {
            $percentiles[(string)$percentile] = $this->calculatePercentile($dataset, $percentile);
        }

        return $percentiles;
    }

    /**
     * @param $data
     * @param $percentile
     * @return float|int
     * @see https://stackoverflow.com/questions/24048879/how-can-i-calculate-the-nth-percentile-from-an-array-of-doubles-in-php
     */
    private function calculatePercentile($data, $percentile)
    {
        $index = $percentile * count($data);
        if (floor($index) == $index) {
            return ($data[$index - 1] + $data[$index]) / 2;
        }

        return $data[(int)floor($index)];
    }
}