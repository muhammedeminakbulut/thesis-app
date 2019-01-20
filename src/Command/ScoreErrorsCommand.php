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

class ScoreErrorsCommand extends Command
{
    /**
     * @var string
     */
    private $scoreErrorsPath;

    /**
     * MergeDataCommand constructor.
     * @param string $scoreErrorsPath
     */
    public function __construct(string $scoreErrorsPath)
    {
        parent::__construct();
        $this->scoreErrorsPath = $scoreErrorsPath;
    }

    public function configure()
    {
        $this->setName('app:score')->setDescription('score error data');
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

        if (!is_file($filePath)) {
            $output->writeln('Not a file');
            return false;
        }

        if (!file_exists($this->scoreErrorsPath)) {
            touch($this->scoreErrorsPath);
        }

        $writer = Writer::createFromPath($this->scoreErrorsPath);
        $writer->insertOne(
            [
                'repository',
                'tag',
                'analyser_loc',
                'analyser_cloc',
                'analyser_cyclomatic_complexity',
                'analyser_duplication',
                'analyser_unit_size',
                'analyser_forks',
                'analyser_contributor',
                'sniffer_errors',
                'sniffer_errors_per_loc',
                'analyser_cloc_per_loc'
            ]
        );

        $reader = Reader::createFromPath($filePath);
        $reader->setHeaderOffset(0);

        foreach ($reader->getRecords() as $record) {
            if ((int)$record['sniffer_errors'] === 0 || (int)$record['analyser_loc'] === 0) {
                $record['sniffer_errors_per_loc'] = 0;
            } else {
                $record['sniffer_errors_per_loc'] = $record['sniffer_errors'] / $record['analyser_loc'];
            }

            if ((int)$record['analyser_loc'] === 0 || (int)$record['analyser_cloc'] === 0) {
                $record['analyser_cloc_per_loc'] = 0;
            } else {
                $record['analyser_cloc_per_loc'] = $record['analyser_cloc'] / $record['analyser_loc'];
            }

            unset($record['analyser_unit_interface_size']);
            unset($record['sniffer_warnings']);

            $writer->insertOne(array_values($record));
        }

        return true;
    }
}