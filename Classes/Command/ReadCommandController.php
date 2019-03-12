<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReadCommandController extends AbstractCommandController
{
    protected static $defaultName = 'document-storage:read';

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $help = 'Read a Document from the database.' . LF . 'If you want to get more detailed information, use the --verbose option.';
        $this->setDescription('Read a Document from the database')
            ->setHelp($help)
            ->addArgument('database', InputArgument::REQUIRED, 'Document database')
            ->addArgument('id', InputArgument::OPTIONAL, 'Document ID')
            ->addOption('count', 'c', InputOption::VALUE_NONE, 'Count the documents in the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = $input->getArgument('database');
        $id = $input->getArgument('id');

        if ($input->getOption('count')) {
            $count = $this->getDocumentRepository()->countByDatabase($db);
            $output->writeln(sprintf('%d documents in database', $count));

            return 0;
        }
        if (!$id) {
            $documents = $this->getDocumentRepository()->findByDatabase($db);
            $this->outputDocuments($output, $documents, !$output->isQuiet());

            return 0;
        }

        $document = $this->getDocument($output, $db, $id);
        if ($document) {
            $this->outputDocument($output, $document, !$output->isQuiet());

            return 0;
        } else {
            return 1;
        }
    }
}
