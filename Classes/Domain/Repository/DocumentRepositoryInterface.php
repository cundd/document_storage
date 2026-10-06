<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Repository;

use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Exception\NoDatabaseSelectedException;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

/**
 * @template T of DocumentInterface
 *
 * @extends RepositoryInterface<T>
 */
interface DocumentRepositoryInterface extends RepositoryInterface
{
    /**
     * Return the Document with the given GUID
     *
     * @param non-empty-string $guid
     *
     * @return T
     */
    public function findByGuid(string $guid): ?DocumentInterface;

    /**
     * Return the Document with the given ID in the given database
     *
     * @param non-empty-string $database
     * @param non-empty-string $id
     *
     * @return T
     */
    public function findOneByDatabaseAndId(
        string $database,
        string $id,
    ): ?DocumentInterface;

    /**
     * Return all objects ignoring the selected database
     *
     * @return T[]|QueryResultInterface<int,T>
     */
    public function findAllIgnoreDatabase(): QueryResultInterface|array;

    /**
     * Search for Documents matching the given properties
     *
     * @param array<non-empty-string,mixed> $properties Dictionary of property keys and values
     * @param int<0,max>                    $limit      Limit the number of matches
     *
     * @return T[]
     *
     * @throws NoDatabaseSelectedException if the converted Document has no database
     */
    public function findWithProperties(
        array $properties,
        int $limit = PHP_INT_MAX,
    ): iterable;
}
