<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Command;

use App\Service\OutlierCleanup;
use App\Service\Percentiles;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Writer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RateDataCommand extends Command
{
    /**
     * @var string
     */
    private $rateDataPath;

    /**
     * RateDataCommand constructor.
     * @param string $rateDataPath
     */
    public function __construct(string $rateDataPath)
    {
        parent::__construct();
        $this->rateDataPath = $rateDataPath;
    }


    public function configure()
    {
        $this->setName('app:rate')->setDescription('score error data');
        $this->setDefinition(
            new InputDefinition(
                [
                    new InputArgument('from-file', InputArgument::REQUIRED),
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

        $reader = Reader::createFromPath($filePath);
        $reader->setHeaderOffset(0);

        $columns = [
            'analyser_loc' => 'desc',
            'analyser_cloc' => 'desc',
            'analyser_cyclomatic_complexity' => 'desc',
            'analyser_duplication' => 'desc',
            'analyser_unit_size' => 'desc',
            'sniffer_errors' => 'desc',
            'analyser_forks' => 'asc',
            'analyser_contributor' => 'asc',
        ];

        $columnPercentile = [];
        foreach ($columns as $column => $sortOrder) {
            $columnPercentile[$column] = $this->getColumnPercentile($reader,  $column, $sortOrder);
        }
        if (!file_exists($this->rateDataPath)) {
            touch($this->rateDataPath);
        }
        $writer = Writer::createFromPath($this->rateDataPath);
        $writer->insertOne([
            'repository',
            'tag',
            'analyser_loc',
            'analyser_cloc',
            'analyser_cyclomatic_complexity',
            'analyser_duplication',
            'analyser_unit_size',
            'sniffer_errors',
            'analyser_forks',
            'analyser_contributor',
        ]);

        foreach($reader->getRecords() as $record) {
            $writer->insertOne([
                    $record['repository'],
                    $record['tag'],
                    Percentiles::classifyScoreDesc($record['analyser_loc'], $columnPercentile['analyser_loc']),
                    Percentiles::classifyScoreDesc($record['analyser_cloc'], $columnPercentile['analyser_cloc']),
                    Percentiles::classifyScoreDesc($record['analyser_cyclomatic_complexity'], $columnPercentile['analyser_cyclomatic_complexity']),
                    Percentiles::classifyScoreDesc($record['analyser_duplication'], $columnPercentile['analyser_duplication']),
                    Percentiles::classifyScoreDesc($record['analyser_unit_size'], $columnPercentile['analyser_unit_size']),
                    Percentiles::classifyScoreDesc($record['sniffer_errors'], $columnPercentile['sniffer_errors']),
                    Percentiles::classifyScoreAsc($record['analyser_forks'], $columnPercentile['analyser_forks']),
                    Percentiles::classifyScoreAsc($record['analyser_contributor'], $columnPercentile['analyser_contributor']),
                ]
            );
        }

        return true;
    }

    private function getColumnPercentile(Reader $reader, $column, $sortOrder)
    {
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

        return Percentiles::calculatePercentiles($data);
    }
}
