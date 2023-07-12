<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Repository;

use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Exception\NoDatabaseSelectedException;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

/**
 */
interface DocumentRepositoryInterface extends RepositoryInterface
{
    /**
     * Return the Document with the given GUID
     *
     * @param string $guid
     * @return DocumentInterface|null
     */
    public function findByGuid(string $guid): ?DocumentInterface;

    /**
     * Return the Document with the given ID in the given database
     *
     * @param string $database
     * @param string $id
     * @return DocumentInterface|null
     */
    public function findOneByDatabaseAndId(string $database, string $id): ?DocumentInterface;

    /**
     * Return all objects ignoring the selected database
     *
     * @return DocumentInterface[]|QueryResultInterface
     */
    public function findAllIgnoreDatabase(): QueryResultInterface|array;

    /**
     * Search for Documents matching the given properties
     *
     * @param array   $properties Dictionary of property keys and values
     * @param integer $limit      Limit the number of matches
     * @return DocumentInterface[]
     * @throws NoDatabaseSelectedException if the converted Document has no database
     */
    public function findWithProperties(array $properties, int $limit = PHP_INT_MAX): iterable;
}
