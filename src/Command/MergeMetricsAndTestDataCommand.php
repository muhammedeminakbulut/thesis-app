<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Command;

use League\Csv\Reader;
use League\Csv\Writer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MergeMetricsAndTestDataCommand extends Command
{
    /**
     * @var string
     */
    private $mergedMetricTestDataPath;

    /**
     * MergeMetricsAndTestDataCommand constructor.
     * @param string $mergedMetricTestDataPath
     */
    public function __construct(string $mergedMetricTestDataPath)
    {
        parent::__construct();
        $this->mergedMetricTestDataPath = $mergedMetricTestDataPath;
    }

    public function configure()
    {
        $this->setName('app:merge-metric-test')->setDescription('merge data');
        $this->setDefinition(
            new InputDefinition(
                [
                    new InputArgument('master-file', InputArgument::REQUIRED),
                    new InputArgument('slave-file', InputArgument::REQUIRED),
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
        $masterFile = $input->getArgument('master-file');
        $slaveFile = $input->getArgument('slave-file');

        if (!is_file($masterFile)) {
            $output->writeln('Master path is not a file');
            return false;
        }
        if (!is_file($slaveFile)) {
            $output->writeln('Master path is not a file');
            return false;
        }


        if (!file_exists($this->mergedMetricTestDataPath)) {
            touch($this->mergedMetricTestDataPath);
        }

        $writer = Writer::createFromPath($this->mergedMetricTestDataPath);
        $writer->insertOne(
            [
                'repository',
                'tag',
                'test_coverage',
                'analyser_loc',
                'analyser_cloc',
                'analyser_cyclomatic_complexity',
                'analyser_duplication',
                'analyser_unit_size',
            ]
        );

        $masterReader = Reader::createFromPath($masterFile);
        $masterReader->setHeaderOffset(0);
        $slaveReader = Reader::createFromPath($slaveFile);
        $slaveReader->setHeaderOffset(0);

        $writer->insertAll($this->matchMasterAndSlave($masterReader->getRecords(), $slaveReader->getRecords()));

        return true;
    }

    private function matchMasterAndSlave($masterRecords, $slaveRecords): array
    {
        $mergedData = [];
        foreach ($masterRecords as $masterRecord) {
            foreach ($slaveRecords as $slaveRecord) {
                if ($masterRecord['repository'] === $slaveRecord['repository'] && $masterRecord['tag'] === $slaveRecord['tag']) {
                    $mergedData[] = array_merge($masterRecord, $slaveRecord);
                }
            }
        }

        return $mergedData;
    }
}
