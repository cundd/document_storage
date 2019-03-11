<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommandController extends AbstractCommandController
{
    protected static $defaultName = 'document-storage:delete';

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $help = 'Remove a Document from the database.' . LF . 'If you want to get more detailed information, use the --verbose option.';
        $this->setDescription('Remove a Document from the database')
            ->setHelp($help)
            ->addArgument('database', InputArgument::REQUIRED, 'Document database')
            ->addArgument('id', InputArgument::REQUIRED, 'Document ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = $input->getArgument('database');
        $id = $input->getArgument('id');

        $document = $this->getDocument($output, $db, $id);
        if ($document) {
            $this->outputDocument($output, $document, !$output->isQuiet());
            $this->getDocumentRepository()->remove($document);
            $this->persistChanges();
            $output->writeln("<info>Removed $db/$id</info>");

            return 0;
        } else {
            return 1;
        }
    }
}
