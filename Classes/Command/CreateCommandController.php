<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Command;

use Cundd\DocumentStorage\Domain\Exception\InvalidDocumentException;
use Cundd\DocumentStorage\Domain\Model\Document;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommandController extends AbstractCommandController
{
    protected static $defaultName = 'document-storage:create';

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $help = 'Create a new Document.' . LF . 'If you want to get more detailed information, use the --verbose option.';
        $this->setDescription('Create a new Document')
            ->setHelp($help)
            ->addArgument('database', InputArgument::REQUIRED, 'Document database')
            ->addArgument('id', InputArgument::REQUIRED, 'Document ID')
            ->addArgument('data', InputArgument::OPTIONAL, 'Document JSON data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $db = $input->getArgument('database');
        $documentRepository = $this->getDocumentRepository();
        if ($documentRepository->findOneByDatabaseAndId($db, $id)) {
            $output->writeln("<error>Document $db/$id already exists</error>");

            return 5;
        }

        $data = $this->getData($input, $output);
        $document = $this->getDataMapper()->mapSingleRow(Document::class, $data);
        $document->setId($id);
        $document->setDb($db);


        $this->outputDocument($output, $document);
        $documentRepository->add($document);
        $this->persistChanges();
        $output->writeln('<info>Saved</info>');

        return 0;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return bool|mixed|string
     */
    protected function getData(InputInterface $input, OutputInterface $output)
    {
        $dataRaw = $input->getArgument('data');
        if (!$dataRaw) {
            $output->writeln('<question>Insert JSON formatted Document:</question> ');
            do {
                $dataRaw .= $this->readRawLine();
            } while (null === json_decode($dataRaw));
        }

        $decoded = json_decode($dataRaw, true);
        if ($decoded === null && strtolower($dataRaw) !== 'null') {
            throw new InvalidDocumentException('Invalid JSON data: ' . json_last_error_msg(), json_last_error());
        }

        return $decoded;
    }

    /**
     * @return string
     */
    protected function readRawLine(): string
    {
        if (function_exists('readline')) {
            /** @noinspection PhpComposerExtensionStubsInspection */
            return readline();
        } else {
            return stream_get_line(STDIN, 1024, PHP_EOL);
        }
    }
}
