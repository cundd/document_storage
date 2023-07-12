<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Command;

use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Exception\InvalidDocumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function file_get_contents;
use function json_decode;
use function json_last_error_msg;
use const STDIN;

class CreateCommandController extends AbstractCommandController
{
    protected function configure(): void
    {
        $help = 'Create a new Document.';
        $this->setDescription('Create a new Document')
            ->setHelp($help)
            ->addArgument('database', InputArgument::REQUIRED, 'Document database')
            ->addArgument('id', InputArgument::REQUIRED, 'Document ID')
            ->addArgument(
                'data',
                InputArgument::OPTIONAL,
                'Document JSON data (otherwise read from STDIN or console input)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $db = $input->getArgument('database');
        $documentRepository = $this->getDocumentRepository();
        if ($documentRepository->findOneByDatabaseAndId($db, $id)) {
            $output->writeln("<error>Document $db/$id already exists</error>");

            return 5;
        }

        $data = $this->getData($input, $output);
        /** @var Document $document */
        $document = $this->dataMapper->mapSingleRow(Document::class, $data);
        $document->setId($id);
        $document->setDb($db);

        $this->outputDocument($input, $output, $document);
        $documentRepository->add($document);
        $this->persistChanges();
        $output->writeln('<info>Saved</info>');

        return self::SUCCESS;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return bool|mixed|string
     */
    protected function getData(InputInterface $input, OutputInterface $output): mixed
    {
        $dataRaw = $input->getArgument('data');
        if (!$dataRaw) {
            $dataRaw = $this->getPipedData();
        }
        if (!$dataRaw) {
            $output->writeln('<question>Insert JSON formatted Document:</question> ');
            do {
                $rawLine = $this->readRawLine();
                if (false === $rawLine) {
                    break;
                }
                $dataRaw .= $this->readRawLine();
            } while (null === json_decode($dataRaw));
        }

        $decoded = json_decode($dataRaw, true);
        if ($decoded === null && strtolower($dataRaw) !== 'null') {
            throw new InvalidDocumentException('Invalid JSON data: ' . json_last_error_msg(), json_last_error());
        }

        return $decoded;
    }

    protected function readRawLine(): string|false
    {
        if (function_exists('readline')) {
            return readline();
        } else {
            return (string)stream_get_line(STDIN, 1024, PHP_EOL);
        }
    }

    protected function getPipedData(): string
    {
        stream_set_blocking(STDIN, false);

        return (string)file_get_contents('php://stdin');
    }
}
