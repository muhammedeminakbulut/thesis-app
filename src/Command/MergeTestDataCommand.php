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

class MergeTestDataCommand extends Command
{
    /**
     * @var string
     */
    private $mergedTestDataPath;

    /**
     * MergeDataCommand constructor.
     * @param string $mergedTestDataPath
     */
    public function __construct(string $mergedTestDataPath)
    {
        parent::__construct();
        $this->mergedTestDataPath = $mergedTestDataPath;
    }

    public function configure()
    {
        $this->setName('app:merge-test')->setDescription('merge data');
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

        if (!file_exists($this->mergedTestDataPath)) {
            touch($this->mergedTestDataPath);
        }

        $writer = Writer::createFromPath($this->mergedTestDataPath);
        $writer->insertOne(
            [
                'repository',
                'tag',
                'coverage',
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

            $writer->insertAll($this->cleanUpRecords($reader->getRecords()));
        }

        return true;
    }

    private function cleanUpRecords($records)
    {
        $returnArray = [];
        foreach ($records as $key => $value) {
            if (strlen($value['coverage']) < 7 && is_numeric($value['coverage'])) {
                $returnArray[] = $value;
            }
        }

        return $returnArray;
    }
}
