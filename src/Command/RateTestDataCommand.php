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

class RateTestDataCommand extends Command
{
    /**
     * @var string
     */
    private $rateTestDataPath;

    /**
     * RateDataCommand constructor.
     * @param string $rateTestDataPath
     */
    public function __construct(string $rateTestDataPath)
    {
        parent::__construct();
        $this->rateTestDataPath = $rateTestDataPath;
    }


    public function configure()
    {
        $this->setName('app:rate-test')->setDescription('score error data');
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
            'coverage' => 'asc',
        ];

        $columnPercentile = [];
        foreach ($columns as $column => $sortOrder) {
            $columnPercentile[$column] = $this->getColumnPercentile($reader,  $column, $sortOrder);
        }
        if (!file_exists($this->rateTestDataPath)) {
            touch($this->rateTestDataPath);
        }
        $writer = Writer::createFromPath($this->rateTestDataPath);
        $writer->insertOne([
            'repository',
            'tag',
            'coverage',
        ]);

        foreach($reader->getRecords() as $record) {
            $writer->insertOne([
                    $record['repository'],
                    $record['tag'],
                    Percentiles::classifyScoreAsc($record['coverage'], $columnPercentile['coverage']),
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
