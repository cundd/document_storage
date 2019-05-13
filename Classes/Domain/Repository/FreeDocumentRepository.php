<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Repository;

use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Exception\InvalidDatabaseNameException;
use Cundd\DocumentStorage\Exception\NoDatabaseSelectedException;
use Cundd\DocumentStorage\Persistence\Repository\AbstractBridge;
use Cundd\DocumentStorage\Persistence\Repository\CoreDocumentRepositoryInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Free Document Repository provides access to any Database (specified in each Document)
 *
 * Managed Documents can belong to any Database. This Repository is more kind of a Object Storage than a traditional
 * Repository.
 * To focus on a single Database use the `DocumentRepository` or the `AbstractDocumentRepository`.
 */
class FreeDocumentRepository extends AbstractBridge
{
    /**
     * Construct a new Document Repository
     *
     * @param ObjectManagerInterface               $objectManager
     * @param CoreDocumentRepositoryInterface|null $baseRepository
     * @param string                               $objectType
     */
    public function __construct(
        ?ObjectManagerInterface $objectManager = null,
        ?CoreDocumentRepositoryInterface $baseRepository = null,
        string $objectType = Document::class
    ) {
        parent::__construct($objectManager, $baseRepository, $objectType);
    }

    /**
     * Return all objects of the given Document database
     *
     * @param string $database
     * @return DocumentInterface[]|QueryResultInterface
     */
    public function findByDatabase(string $database)
    {
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        return $this->baseRepository->findByDatabase($database);
    }

    /**
     * Count all objects of the given Document database
     *
     * @param string $database
     * @return int
     */
    public function countByDatabase(string $database): int
    {
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        return $this->baseRepository->countByDatabase($database);
    }

    /**
     * Remove all Documents from the given database
     *
     * @param string $database
     * @return void
     */
    public function removeAllFromDatabase(string $database): void
    {
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        $this->baseRepository->removeAllFromDatabase($database);
    }

    public function findAll(string $database = null)
    {
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        return $this->baseRepository->findByDatabase($database);
    }

    public function countAll(string $database = null)
    {
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        return $this->baseRepository->countByDatabase($database);
    }

    public function removeAll(string $database = null)
    {
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        $this->baseRepository->removeAllFromDatabase($database);
    }

    public function findWithProperties(array $properties, int $limit = PHP_INT_MAX): iterable
    {
        if (isset($properties['db'])) {
            $database = $properties['db'];
        } elseif (isset($properties['database'])) {
            $database = $properties['database'];
        } else {
            throw new NoDatabaseSelectedException('Missing key "database"');
        }
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        return $this->baseRepository->findWithProperties(
            array_merge(
                $properties,
                ['db' => $database]
            ),
            $limit
        );
    }

    /**
     * @param DocumentInterface $object
     * @return DocumentInterface
     */
    protected function checkDocumentDatabase(DocumentInterface $object): DocumentInterface
    {
        if (!$object->getDb()) {
            throw new NoDatabaseSelectedException(
                'The given object has no database set',
                1389257938
            );
        }

        return $object;
    }
}
