<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Persistence\Repository;

use BadMethodCallException;
use Cundd\DocumentStorage\DocumentFilter;
use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Domain\ValueObject\DatabaseName;
use Cundd\DocumentStorage\Exception\InvalidDatabaseNameException;
use Cundd\DocumentStorage\Exception\InvalidIdException;
use Cundd\DocumentStorage\Exception\NoDatabaseSelectedException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Generic Document Repository implementation to be used by concrete
 * implementations like `FreeDocumentRepository`or `DocumentRepository`
 *
 * @internal
 *
 * @template T of DocumentInterface & \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
 *
 * @extends Repository<T>
 *
 * @implements CoreDocumentRepositoryInterface<T>
 */
final class CoreDocumentRepository extends Repository implements CoreDocumentRepositoryInterface
{
    /**
     * Concrete Repository implementation which will be used by `DocumentRepository` and `FreeDocumentRepository`
     *
     * @param class-string<T> $objectType
     */
    private function __construct(
        string $objectType,
        private readonly DocumentFilter $documentFilter,
        PersistenceManagerInterface $persistenceManager,
    ) {
        parent::__construct();
        $this->injectPersistenceManager($persistenceManager);
        $this->objectType = $objectType;
    }

    /**
     * Factory to build the Base Document Repository
     *
     * @template R of DocumentInterface & \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
     *
     * @param class-string<R> $objectType
     *
     * @return self<R>
     *
     * @internal
     */
    public static function build(
        string $objectType,
        DocumentFilter $documentFilter,
        PersistenceManagerInterface $persistenceManager,
    ): CoreDocumentRepositoryInterface {
        return new static(
            $objectType,
            $documentFilter,
            $persistenceManager
        );
    }

    /**
     * Add an object to this repository
     *
     * @param T $object The object to add
     *
     * @throws NoDatabaseSelectedException if the given object and the repository have no database set
     */
    public function add($object): void
    {
        $object = $this->checkDocumentDatabase($object);
        $this->willChangeDocument($object);
        parent::add($object);
        $this->didChangeDocument($object);
    }

    /**
     * Remove an object from this repository
     *
     * @param T $object The object to remove
     *
     * @throws NoDatabaseSelectedException if the given object and the repository have no database set
     */
    public function remove($object): void
    {
        $object = $this->checkDocumentDatabase($object);
        $this->willChangeDocument($object);

        $object->setId(uniqid() . '-' . time() . '-' . $object->getId());
        $object->setDeleted(true);
        parent::update($object);
        $this->didChangeDocument($object);
    }

    /**
     * Replace an existing object with the same identifier by the given object
     *
     * @param T $modifiedObject The modified object
     *
     * @throws NoDatabaseSelectedException if the given object and the repository have no database set
     */
    public function update($modifiedObject): void
    {
        $modifiedObject = $this->checkDocumentDatabase($modifiedObject);
        $this->willChangeDocument($modifiedObject);
        parent::update($modifiedObject);
        $this->didChangeDocument($modifiedObject);
    }

    /**
     * Return all objects of the selected Document database
     *
     * @param non-empty-string $database
     *
     * @return T[]|QueryResultInterface<int,T>
     */
    public function findAll(?string $database = null): iterable
    {
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        return $this->findByDatabase(new DatabaseName($database));
    }

    public function findByGuid(string $guid): ?DocumentInterface
    {
        [$database, $id] = $this->splitGuid($guid);

        return $this->findOneByDatabaseAndId($database, $id);
    }

    public function findOneById(string $id): ?DocumentInterface
    {
        /** @var Query<T> $query */
        $query = $this->createQuery();

        return $this->findOneByConstraint($query, $query->equals('id', $id));
    }

    public function findById(string $id): ?DocumentInterface
    {
        return $this->findOneById($id);
    }

    /**
     * Return all objects ignoring the current Document database
     *
     * @return QueryResultInterface<int,T>
     */
    public function findAllIgnoreDatabase(): QueryResultInterface
    {
        return $this->createQuery()->execute(false);
    }

    /**
     * Search for Documents matching the given properties
     *
     * If `addDataConstraint` is `TRUE` the SQL `dataProtected LIKE '%propertyValue%'` will be added.
     * To use the documentFilter only for filtering set it to FALSE
     *
     * @param array<non-empty-string,mixed> $properties        Dictionary of property keys and values
     * @param int                           $limit             Limit the number of matches
     * @param bool                          $addDataConstraint If `TRUE` the SQL `dataProtected LIKE '%propertyValue%'` will be added
     *
     * @return T[]
     */
    public function findWithProperties(
        array $properties,
        int $limit = PHP_INT_MAX,
        bool $addDataConstraint = true,
    ): iterable {
        $query = $this->createQuery();

        $constraintsCollection = [];
        if (isset($properties['guid'])) {
            $guid = $properties['guid'];
            [$database, $id] = $this->splitGuid($guid);
            $constraintsCollection[] = $query->equals('db', $database);
            $constraintsCollection[] = $query->equals('id', $id);
            unset($properties['guid']);

            // TODO: Check if `id` or `db` are also queried inside `$properties`
        }

        // TODO: Check if `id` column should be added
        $realDatabaseColumns = ['uid', 'pid', 'tstamp', 'crdate', 'cruser_id', 'db'];
        foreach ($realDatabaseColumns as $realDatabaseColumn) {
            if (isset($properties[$realDatabaseColumn])) {
                $constraintsCollection[] = $query->equals(
                    $realDatabaseColumn,
                    $properties[$realDatabaseColumn]
                );
                unset($properties[$realDatabaseColumn]);
            }
        }
        if ($addDataConstraint) {
            foreach ($properties as $propertyValue) {
                $constraintsCollection[] = $query->like(
                    'dataProtected',
                    '%' . $propertyValue . '%'
                );
            }
        }
        if (!empty($constraintsCollection)) {
            $query->matching($query->logicalAnd(...$constraintsCollection));
        }

        // Get the objects
        $resultCollection = $query->execute(false);

        // Filter using the remaining properties
        return $this->documentFilter->filterByProperties(
            $resultCollection,
            $properties,
            $limit
        );
    }

    /**
     * Return all objects of the given Document database
     *
     * @return QueryResultInterface<int,T>
     */
    public function findByDatabase(DatabaseName $database): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('db', $database));

        return $query->execute(false);
    }

    /**
     * Return all objects of the given Document database
     */
    public function countByDatabase(DatabaseName $database): int
    {
        $query = $this->createQuery();
        $query->matching($query->equals('db', $database));

        return $query->count();
    }

    /**
     * Return the Document with the given ID in the given database
     *
     * @return T|null
     */
    public function findOneByDatabaseAndId(string $database, string $id): ?DocumentInterface
    {
        /** @var Query<T> $query */
        $query = $this->createQuery();
        InvalidDatabaseNameException::assertValidDatabaseName($database);
        InvalidIdException::assertValidId($id);
        $constraint = $query->logicalAnd(
            $query->equals('db', $database),
            $query->equals('id', $id)
        );

        return $this->findOneByConstraint($query, $constraint);
    }

    /**
     * Remove all Documents from the given database
     */
    public function removeAllFromDatabase(DatabaseName $database): void
    {
        foreach ($this->findByDatabase($database) as $document) {
            $this->remove($document);
        }
    }

    /**
     * @param Query<T> $query
     *
     * @return T|null
     */
    private function findOneByConstraint(
        Query $query,
        ConstraintInterface $constraint,
    ): ?DocumentInterface {
        $query->matching(
            $constraint
        );

        $query->setLimit(1);

        return $query->execute()->getFirst();
    }

    /**
     * Return a query for objects of this repository
     *
     * @return QueryInterface<T>
     */
    public function createQuery(): QueryInterface
    {
        $query = parent::createQuery();
        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectSysLanguage(false);
        $querySettings->setRespectStoragePage(false);

        return $query;
    }

    /**
     * Dispatches magic methods (findBy[Property]())
     *
     * @param list<mixed> $arguments
     */
    public function __call(string $methodName, array $arguments): void
    {
        throw new BadMethodCallException(
            'Method "' . $methodName . '" is not supported by the repository',
            1233180480
        );
    }

    /**
     * Invoked before a Document in the repository will be changed
     */
    private function willChangeDocument(DocumentInterface $document): void
    {
        // noop
    }

    /**
     * Invoked after a Document in the repository will be changed
     */
    private function didChangeDocument(DocumentInterface $document): void
    {
        // noop
    }

    /**
     * @param T $object
     *
     * @return T
     */
    private function checkDocumentDatabase(
        DocumentInterface $object,
    ): DocumentInterface {
        if (!$object->getDb()) {
            throw new NoDatabaseSelectedException(
                'The given object has no database set',
                1389257938
            );
        }

        return $object;
    }

    /**
     * @param non-empty-string $guid
     *
     * @return array{0:non-empty-string,1:non-empty-string}
     */
    private function splitGuid(string $guid): array
    {
        [$database, $id] = explode('/', $guid, 2);
        InvalidDatabaseNameException::assertValidDatabaseName($database);
        InvalidIdException::assertValidId($id);

        return [$database, $id];
    }
}
