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
use MathPHP\Statistics\Correlation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CorrelateCommand extends Command
{
    public function configure()
    {
        $this->setName('app:correlate')->setDescription('score error data');
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
        [
            'repository',
            'tag',
            'analyser_loc',
            'analyser_cloc',
            'analyser_cyclomatic_complexity',
            'analyser_duplication',
            'analyser_unit_size',
            'sniffer_errors',
            'sniffer_errors_per_loc',
            'analyser_forks',
            'analyser_contributor',
        ];
        //avg of scores
        $scores = [];
        foreach ($reader->getRecords() as $record) {
            $scores[] = round(
                ((float) $record['analyser_loc'] +
                (float) $record['analyser_cloc'] +
                (float) $record['analyser_cyclomatic_complexity'] +
                (float) $record['analyser_duplication'] +
                (float) $record['analyser_unit_size']) / 5
            );
        }


        $table = new Table($output);
        $table
            ->setHeaders(['X', 'Y', 'Correlation'])
            ->setRows([
                ['Loc', 'Sniffer errors', Correlation::spearmansRho($this->getColumnData($reader, 'analyser_loc'), $this->getColumnData($reader, 'sniffer_errors'))],
                ['Cloc', 'Sniffer errors', Correlation::spearmansRho($this->getColumnData($reader, 'analyser_cloc'), $this->getColumnData($reader, 'sniffer_errors'))],
                ['Cyclomatic complexity', 'Sniffer errors', Correlation::spearmansRho($this->getColumnData($reader, 'analyser_cyclomatic_complexity'), $this->getColumnData($reader, 'sniffer_errors'))],
                ['Duplication', 'Sniffer errors', Correlation::spearmansRho($this->getColumnData($reader, 'analyser_duplication'), $this->getColumnData($reader, 'sniffer_errors'))],
                ['Average Unit size', 'Sniffer errors', Correlation::spearmansRho($this->getColumnData($reader, 'analyser_unit_size'), $this->getColumnData($reader, 'sniffer_errors'))],
                ['Combined scores', 'Sniffer errors', Correlation::spearmansRho($scores, $this->getColumnData($reader, 'sniffer_errors'))],
                ['Combined scores', 'Sniffer errors per loc', Correlation::spearmansRho($scores, $this->getColumnData($reader, 'sniffer_errors_per_loc'))],
                ['Combined scores', 'Forks', Correlation::spearmansRho($scores, $this->getColumnData($reader, 'analyser_forks'))],
                ['Combined scores', 'Contributors', Correlation::spearmansRho($scores, $this->getColumnData($reader, 'analyser_contributor'))],
            ])
        ;
        $table->render();

        return true;
    }

    private function getColumnData(Reader $reader, $column)
    {
        $records = (new Statement())->process($reader);
        $columnData = [];
        foreach ($records->fetchColumn($column) as $value) {
            $columnData[] = (float)$value;
        }

        return $columnData;
    }
}