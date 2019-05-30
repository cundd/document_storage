<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Command;

use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Domain\Repository\DatabaseRepository;
use Cundd\DocumentStorage\Domain\Repository\FreeDocumentRepository;
use Cundd\DocumentStorage\Persistence\DataMapper;
use Cundd\DocumentStorage\Persistence\Repository\CoreDocumentRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

abstract class AbstractCommandController extends Command
{
    use DocumentOutputTrait;

    /**
     * Document repository
     *
     * @var FreeDocumentRepository
     */
    private $documentRepository;

    /**
     * @var DatabaseRepository
     */
    private $databaseRepository;

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
        /** @var Document $document */
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
            $baseDocumentRepository = CoreDocumentRepository::build($objectManager);
            $this->documentRepository = new FreeDocumentRepository($objectManager, $baseDocumentRepository);
        }

        return $this->documentRepository;
    }

    protected function getDatabaseRepository(): DatabaseRepository
    {
        if (!$this->databaseRepository) {
            $objectManager = $this->getObjectManager();
            $this->databaseRepository = new DatabaseRepository(
                $objectManager,
                $objectManager->get(ConnectionPool::class)
            );
        }

        return $this->databaseRepository;
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
