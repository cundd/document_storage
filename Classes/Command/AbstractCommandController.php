<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Command;

use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Domain\Repository\BaseDocumentRepository;
use Cundd\DocumentStorage\Domain\Repository\FreeDocumentRepository;
use Cundd\DocumentStorage\Persistence\DataMapper;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

abstract class AbstractCommandController extends \Symfony\Component\Console\Command\Command
{
    /**
     * Document repository
     *
     * @var FreeDocumentRepository
     */
    private $documentRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataMapper
     */
    private $dataMapper;

    protected function getDocument(OutputInterface $output, string $db, string $id): ?Document
    {
        $document = $this->getDocumentRepository()->findOneByDatabaseAndId($db, $id);
        if ($document) {
            return $document;
        } else {

            $output->writeln("<error>Document $db/$id not found</error>");

            return null;
        }
    }

    protected function getDocumentRepository(): FreeDocumentRepository
    {
        if (!$this->documentRepository) {
            $objectManager = $this->getObjectManager();
            $baseDocumentRepository = BaseDocumentRepository::build(
                $objectManager,
                $objectManager->get(ConnectionPool::class)
            );
            $this->documentRepository = new FreeDocumentRepository($objectManager, $baseDocumentRepository);
        }

        return $this->documentRepository;
    }

    protected function getDataMapper(): DataMapper
    {
        if (!$this->dataMapper) {
            $this->dataMapper = $this->getObjectManager()->get(DataMapper::class);
        }

        return $this->dataMapper;
    }

    protected function persistChanges()
    {
        $this->getObjectManager()->get(PersistenceManagerInterface::class)->persistAll();
    }

    /**
     * Returns a formatted json-encoded version of the given data
     *
     * @param mixed $data         The data to format
     * @param bool  $isJsonString Set this to TRUE if the given data already is a JSON string
     * @return string
     */
    protected function formatJsonData($data, bool $isJsonString = false)
    {
        if ($isJsonString) {
            $data = json_decode((string)$data, true);
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Displays information about the given Documents
     *
     * @param OutputInterface $output
     * @param iterable        $documents
     * @param bool            $showBody
     */
    protected function outputDocuments(OutputInterface $output, iterable $documents, bool $showBody = false): void
    {
        foreach ($documents as $document) {
            $this->outputDocument($output, $document, $showBody);
        }
    }

    /**
     * Displays information about the given Document
     *
     * @param OutputInterface $output
     * @param Document        $document
     * @param bool            $showBody
     */
    protected function outputDocument(OutputInterface $output, Document $document, bool $showBody = false): void
    {
        $output->writeln(
            '<info>'
            . 'Database: ' . $document->getDb() . ' '
            . 'ID: ' . ($document->getId() ? $document->getId() : '(Missing ID)') . ' '
            . '</info>'
        );

        if ($showBody) {
            $data = $document->getDataProtected() ?? '{}';
            $output->writeln($this->formatJsonData($data, true) . PHP_EOL);
        }
    }

    /**
     * @return ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        if (!$this->objectManager) {
            $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        }

        return $this->objectManager;
    }
}
