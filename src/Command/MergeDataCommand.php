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

class MergeDataCommand extends Command
{
    /**
     * @var string
     */
    private $mergedDataPath;

    /**
     * MergeDataCommand constructor.
     * @param string $mergedDataPath
     */
    public function __construct(string $mergedDataPath)
    {
        parent::__construct();
        $this->mergedDataPath = $mergedDataPath;
    }

    public function configure()
    {
        $this->setName('app:merge')->setDescription('merge data');
        $this->setDefinition(
            new InputDefinition(
                [
                    new InputArgument('from-directory', InputArgument::REQUIRED),
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
        $fromDirectory = $input->getArgument('from-directory');

        if (!is_dir($fromDirectory)) {
            $output->writeln('Not a directory');
            return false;
        }

        $files = scandir($fromDirectory);

        if (!file_exists($this->mergedDataPath)) {
            touch($this->mergedDataPath);
        }

        $writer = Writer::createFromPath($this->mergedDataPath);
        $writer->insertOne(
            [
                'repository',
                'tag',
                'analyser_loc',
                'analyser_cloc',
                'analyser_cyclomatic_complexity',
                'analyser_duplication',
                'analyser_unit_size',
                'analyser_unit_interface_size',
                'analyser_forks',
                'analyser_contributor',
                'sniffer_errors',
                'sniffer_warnings',
            ]
        );

        foreach ($files as $fileName) {
            if ($fileName === '.' || $fileName === '..' || $fileName === '.gitignore') {
                continue;
            }

            $filePath = sprintf('%s/%s', $fromDirectory, $fileName);

            if (!is_file($filePath)) {
                continue;
            }

            $reader = Reader::createFromPath($filePath);
            $reader->setHeaderOffset(0);

            $writer->insertAll($reader->getRecords());
        }

        return true;
    }
}
