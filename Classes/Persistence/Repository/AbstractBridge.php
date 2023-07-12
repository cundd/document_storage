<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Persistence\Repository;

use BadFunctionCallException;
use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Domain\Repository\DocumentRepositoryInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * The Bridge builds the connection between the various Document repository implementations and the concrete Core
 * Document Repository
 */
abstract class AbstractBridge implements DocumentRepositoryInterface
{
    protected CoreDocumentRepositoryInterface $baseRepository;

    /**
     * Construct a new Document Repository
     *
     * @param CoreDocumentRepositoryInterface $baseRepository
     * @param string                          $objectType
     */
    public function __construct(
        CoreDocumentRepositoryInterface $baseRepository,
        string $objectType = Document::class
    ) {
        $this->baseRepository = $baseRepository;
    }

    /**
     * @param DocumentInterface $object
     * @return DocumentInterface
     */
    abstract protected function checkDocumentDatabase(DocumentInterface $object): DocumentInterface;

    /**
     * @param DocumentInterface $object
     * @inheritDoc
     */
    public function add($object)
    {
        $this->baseRepository->add($this->checkDocumentDatabase($object));
    }

    /**
     * @param DocumentInterface $object
     * @inheritDoc
     */
    public function remove($object)
    {
        $this->baseRepository->remove($this->checkDocumentDatabase($object));
    }

    /**
     * @param DocumentInterface $modifiedObject
     * @inheritDoc
     */
    public function update($modifiedObject)
    {
        $this->baseRepository->update($this->checkDocumentDatabase($modifiedObject));
    }

    public function setDefaultOrderings(array $defaultOrderings)
    {
        throw new BadFunctionCallException(__METHOD__ . ' is not implemented');
    }

    public function setDefaultQuerySettings(QuerySettingsInterface $defaultQuerySettings)
    {
        throw new BadFunctionCallException(__METHOD__ . ' is not implemented');
    }

    public function createQuery()
    {
        throw new BadFunctionCallException(__METHOD__ . ' is not implemented');
    }

    public function findByGuid(string $guid): ?DocumentInterface
    {
        return $this->baseRepository->findByGuid($guid);
    }

    public function findOneByDatabaseAndId(string $database, string $id): ?DocumentInterface
    {
        return $this->baseRepository->findOneByDatabaseAndId($database, $id);
    }

    public function findAllIgnoreDatabase(): QueryResultInterface|array
    {
        return $this->baseRepository->findAllIgnoreDatabase();
    }
}
