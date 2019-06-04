<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function sprintf;

class ReadCommandController extends AbstractCommandController
{
    protected function configure()
    {
        $help = 'Display a Document/all Documents from the database.';
        $this->setDescription('Read Documents from the database')
            ->setHelp($help)
            ->addArgument('database', InputArgument::REQUIRED, 'Name of the database')
            ->addArgument('id', InputArgument::OPTIONAL, 'ID of the Document to show (omit to show all)')
            ->addOption('count', 'c', InputOption::VALUE_NONE, 'Count the Documents in the database')
            ->addOption('short', 's', InputOption::VALUE_NONE, 'Show only the headers of the Documents')
            ->addOption(
                'key-paths',
                'k',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Key-path(s) of Document values to output'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = $input->getArgument('database');
        $id = $input->getArgument('id');
        $keyPaths = $input->getOption('key-paths');

        $short = $input->getOption('short');
        if ($input->getOption('count')) {
            $count = $this->getDocumentRepository()->countByDatabase($db);
            $output->writeln(sprintf('%d documents in database', $count));

            return 0;
        }
        if (!$id) {
            $documents = $this->getDocumentRepository()->findByDatabase($db);
            if (count($documents) === 0) {
                $output->writeln(sprintf('Database "%s" not found', $db));

                return 1;
            }

            $this->outputDocuments($input, $output, $documents, !$short, $keyPaths);

            return 0;
        }

        $document = $this->getDocument($output, $db, $id);
        if ($document) {
            $this->outputDocument($input, $output, $document, !$short, $keyPaths);

            return 0;
        } else {
            return 1;
        }
    }
}
