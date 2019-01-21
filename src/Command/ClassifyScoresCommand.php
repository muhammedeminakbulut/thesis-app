<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Command;

use App\Service\OutlierCleanup;
use App\Service\Percentiles;
use League\Csv\Reader;
use League\Csv\Statement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClassifyScoresCommand extends Command
{
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

        $data = OutlierCleanup::removeOutliers($columnData);

        if ($sortOrder === 'asc') {
            sort($data);
        } elseif ($sortOrder === 'desc') {
            rsort($data);
        }

        $percentiles = Percentiles::calculatePercentiles($data);

        foreach ($percentiles as $key => $value) {
            $output->writeln(sprintf('percentile %s: %s', $key, $value));
        }

        return true;
    }
}