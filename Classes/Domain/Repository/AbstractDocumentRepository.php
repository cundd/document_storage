<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Repository;

use Cundd\DocumentStorage\Domain\Model\Document;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;

/**
 */
abstract class AbstractDocumentRepository implements DocumentRepositoryInterface
{
    /**
     * @var BaseDocumentRepository
     */
    protected $baseRepository;

    /**
     * Constructs a new Repository
     *
     * @param ObjectManagerInterface      $objectManager
     * @param BaseDocumentRepository|null $baseRepository
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ?BaseDocumentRepository $baseRepository = null
    ) {
        $this->baseRepository = $baseRepository ?? BaseDocumentRepository::build(
                $objectManager,
                $objectManager->get(ConnectionPool::class)
            );
    }

    /**
     * @param Document|object $object
     * @return Document
     */
    abstract protected function checkDocumentDatabase(Document $object): Document;

    public function add($object)
    {
        $this->baseRepository->add($this->checkDocumentDatabase($object));
    }

    public function remove($object)
    {
        $this->baseRepository->remove($this->checkDocumentDatabase($object));
    }

    public function update($modifiedObject)
    {
        $this->baseRepository->update($this->checkDocumentDatabase($modifiedObject));
    }

    public function findByUid($uid)
    {
        return $this->baseRepository->findByUid($uid);
    }

    public function findByIdentifier($identifier)
    {
        return $this->findByGuid($identifier);
    }

    public function setDefaultOrderings(array $defaultOrderings)
    {
        throw new \BadFunctionCallException(__METHOD__ . ' is not implemented');
    }

    public function setDefaultQuerySettings(QuerySettingsInterface $defaultQuerySettings)
    {
        throw new \BadFunctionCallException(__METHOD__ . ' is not implemented');
    }

    public function createQuery()
    {
        throw new \BadFunctionCallException(__METHOD__ . ' is not implemented');
    }

    public function findByGuid(string $guid): ?Document
    {
        return $this->baseRepository->findByGuid($guid);
    }

    public function findOneByDatabaseAndId(string $database, string $id): ?Document
    {
        return $this->baseRepository->findOneByDatabaseAndId($database, $id);
    }

    public function findById($id)
    {
        return $this->findOneById($id);
    }

    public function findAllIgnoreDatabase()
    {
        return $this->baseRepository->findAllIgnoreDatabase();
    }
}
