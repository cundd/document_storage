<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Repository;

use Cundd\DocumentStorage\Domain\Exception\NoDatabaseSelectedException;
use Cundd\DocumentStorage\Domain\Model\Document;
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
     * @return Document
     */
    public function findByGuid(string $guid): ?Document;

    /**
     * Return the Document with the given ID in the given database
     *
     * @param string $database
     * @param string $id
     * @return Document
     */
    public function findOneByDatabaseAndId(string $database, string $id): ?Document;

    /**
     * Return the Document with the given ID
     *
     * @param string $id
     * @return Document
     */
    public function findOneById(string $id);

    /**
     * @see findOneById()
     * @param $id
     * @return Document
     */
    public function findById(string $id);

    /**
     * Return all objects ignoring the selected database
     *
     * @return Document[]|QueryResultInterface
     */
    public function findAllIgnoreDatabase();

    /**
     * Search for Documents matching the given properties
     *
     * @param array   $properties Dictionary of property keys and values
     * @param integer $limit      Limit the number of matches
     * @throws NoDatabaseSelectedException if the converted Document has no database
     * @return array
     */
    public function findWithProperties(array $properties, int $limit = PHP_INT_MAX): array;
}