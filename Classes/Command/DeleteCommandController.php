<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommandController extends AbstractCommandController
{
    protected function configure(): void
    {
        $help = 'Remove a Document from the database.';
        $this->setDescription('Remove a Document from the database')
            ->setHelp($help)
            ->addArgument('database', InputArgument::REQUIRED, 'Document database')
            ->addArgument('id', InputArgument::REQUIRED, 'Document ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $db = $input->getArgument('database');
        $id = $input->getArgument('id');

        $document = $this->getDocument($output, $db, $id);
        if ($document) {
            $this->outputDocument($input, $output, $document, !$output->isQuiet());
            $this->getDocumentRepository()->remove($document);
            $this->persistChanges();
            $output->writeln("<info>Removed $db/$id</info>");

            return self::SUCCESS;
        } else {
            return self::FAILURE;
        }
    }
}
