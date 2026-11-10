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
 * The Bridge builds the connection between the various Document repository
 * implementations and the concrete Core Document Repository
 *
 * @template T of DocumentInterface
 *
 * @implements DocumentRepositoryInterface<T>
 *
 * @internal
 */
abstract class AbstractBridge implements DocumentRepositoryInterface
{
    /**
     * Construct a new Document Repository
     *
     * @param CoreDocumentRepositoryInterface<T> $baseRepository
     * @param class-string<T>                    $objectType
     */
    public function __construct(
        protected readonly CoreDocumentRepositoryInterface $baseRepository,
        protected readonly string $objectType = Document::class,
    ) {
    }

    /**
     * @param T $object
     *
     * @return T
     */
    abstract protected function checkDocumentDatabase(
        DocumentInterface $object,
    ): DocumentInterface;

    /**
     * @param T $object
     */
    public function add($object): void
    {
        $this->baseRepository->add($this->checkDocumentDatabase($object));
    }

    /**
     * @param T $object
     */
    public function remove($object): void
    {
        $this->baseRepository->remove($this->checkDocumentDatabase($object));
    }

    /**
     * @param T $modifiedObject
     */
    public function update($modifiedObject): void
    {
        $this->baseRepository->update($this->checkDocumentDatabase($modifiedObject));
    }

    /**
     * @param array<non-empty-string,'ASC'|'DESC'> $defaultOrderings
     */
    public function setDefaultOrderings(array $defaultOrderings): void
    {
        throw new BadFunctionCallException(__METHOD__ . ' is not implemented');
    }

    public function setDefaultQuerySettings(QuerySettingsInterface $defaultQuerySettings): void
    {
        throw new BadFunctionCallException(__METHOD__ . ' is not implemented');
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface<T>
     */
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
