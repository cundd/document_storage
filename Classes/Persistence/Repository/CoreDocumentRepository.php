<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Persistence\Repository;

use Cundd\DocumentStorage\DocumentFilter;
use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Exception\InvalidDatabaseNameException;
use Cundd\DocumentStorage\Exception\InvalidIdException;
use Cundd\DocumentStorage\Exception\NoDatabaseSelectedException;
use Cundd\DocumentStorage\Persistence\DataMapper;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Generic Document Repository implementation to be used by concrete implementations like `FreeDocumentRepository`or
 * `DocumentRepository`
 *
 * @internal
 */
class CoreDocumentRepository extends Repository implements CoreDocumentRepositoryInterface
{
    /**
     * Defines if query results should be retrieved raw and converted by convertCollection()
     *
     * @var bool
     */
    private $useCustomDataMapping = false;

    /**
     * @var DataMapper|null
     */
    private $dataMapper;

    /**
     * @var DocumentFilter|null
     */
    private $documentFilter;

    /**
     * Concrete Repository implementation which will be used by `DocumentRepository` and `FreeDocumentRepository`
     *
     * @param ObjectManagerInterface $objectManager
     * @param string                 $objectType
     * @param DataMapper|null        $dataMapper
     * @param DocumentFilter|null    $documentFilter
     */
    private function __construct(
        ObjectManagerInterface $objectManager,
        string $objectType = Document::class,
        ?DataMapper $dataMapper = null,
        ?DocumentFilter $documentFilter = null
    ) {
        parent::__construct($objectManager);
        $this->injectPersistenceManager($objectManager->get(PersistenceManagerInterface::class));
        $this->dataMapper = $dataMapper ?? $objectManager->get(DataMapper::class);
        $this->objectType = $objectType;
        $this->documentFilter = $documentFilter ?? $objectManager->get(DocumentFilter::class);
    }

    /**
     * Factory to build the Base Document Repository
     *
     * @param ObjectManagerInterface $objectManager
     * @param string                 $objectType
     * @param DataMapper|null        $dataMapper
     * @return CoreDocumentRepositoryInterface
     * @internal
     */
    public static function build(
        ObjectManagerInterface $objectManager,
        string $objectType = Document::class,
        ?DataMapper $dataMapper = null
    ): CoreDocumentRepositoryInterface {
        return new static($objectManager, $objectType, $dataMapper);
    }

    /**
     * Add an object to this repository
     *
     * @param DocumentInterface $object The object to add
     * @return void
     * @throws NoDatabaseSelectedException if the given object and the repository have no database set
     */
    public function add($object)
    {
        $object = $this->checkDocumentDatabase($object);
        $this->willChangeDocument($object);
        parent::add($object);
        $this->didChangeDocument($object);
    }

    /**
     * Remove an object from this repository
     *
     * @param DocumentInterface $object The object to remove
     * @return void
     * @throws NoDatabaseSelectedException if the given object and the repository have no database set
     */
    public function remove($object)
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
     * @param DocumentInterface $modifiedObject The modified object
     * @return void
     * @throws NoDatabaseSelectedException if the given object and the repository have no database set
     */
    public function update($modifiedObject)
    {
        $modifiedObject = $this->checkDocumentDatabase($modifiedObject);
        $this->willChangeDocument($modifiedObject);
        parent::update($modifiedObject);
        $this->didChangeDocument($modifiedObject);
    }

    /**
     * Return all objects of the selected Document database
     *
     * @param string|null $database
     * @return DocumentInterface[]|QueryResultInterface
     */
    public function findAll(string $database = null)
    {
        return $this->findByDatabase($this->prepareDatabaseArgument($database));
    }

    public function findByGuid(string $guid): ?DocumentInterface
    {
        list($database, $id) = $this->splitGuid($guid);

        return $this->findOneByDatabaseAndId($database, $id);
    }

    public function findOneById(string $id)
    {
        /** @var Query $query */
        $query = $this->createQuery();

        return $this->findOneByConstraint($query, $query->equals('id', $id));
    }

    public function findById(string $id)
    {
        return $this->findOneById($id);
    }

    public function findAllIgnoreDatabase()
    {
        return $this->convertQueryResult($this->createQuery());
    }

    /**
     * Search for Documents matching the given properties
     *
     * If `addDataConstraint` is TRUE the SQL `dataProtected LIKE '%propertyValue%'` will be added.
     * To use the documentFilter only for filtering set it to FALSE
     *
     * @param array   $properties        Dictionary of property keys and values
     * @param integer $limit             Limit the number of matches
     * @param bool    $addDataConstraint If TRUE the SQL `dataProtected LIKE '%propertyValue%'` will be added
     * @return DocumentInterface[]
     */
    public function findWithProperties(
        array $properties,
        int $limit = PHP_INT_MAX,
        bool $addDataConstraint = true
    ): iterable {
        $query = $this->createQuery();

        $constraintsCollection = [];
        if (isset($properties['guid'])) {
            $guid = $properties['guid'];
            list($database, $id) = $this->splitGuid($guid);
            $constraintsCollection[] = $query->equals('db', $database);
            $constraintsCollection[] = $query->equals('id', $id);
            unset($properties['guid']);
        }

        $realDatabaseColumns = ['uid', 'pid', 'tstamp', 'crdate', 'cruser_id', 'db'];
        foreach ($realDatabaseColumns as $realDatabaseColumn) {
            if (isset($properties[$realDatabaseColumn])) {
                $constraintsCollection[] = $query->equals($realDatabaseColumn, $properties[$realDatabaseColumn]);
                unset($properties[$realDatabaseColumn]);
            }
        }
        if ($addDataConstraint) {
            foreach ($properties as $property => $propertyValue) {
                $constraintsCollection[] = $query->like('dataProtected', '%' . $propertyValue . '%');
            }
        }
        if (!empty($constraintsCollection)) {
            $query->matching($query->logicalAnd($constraintsCollection));
        }

        // Get the objects
        $resultCollection = $this->convertQueryResult($query);

        // Filter using the remaining properties
        return $this->documentFilter->filterByProperties($resultCollection, $properties, $limit);
    }

    /**
     * Return all data of the selected Document database
     *
     * Like findAll() but will return the raw database tables
     *
     * @param string $database
     * @return array[]
     */
    public function findAllRaw(string $database)
    {
        $query = $this->createQuery();
        $query->matching($query->equals('db', $this->prepareDatabaseArgument($database)));

        return $query->execute($this->useCustomDataMapping);
    }

    /**
     * Return all objects of the given Document database
     *
     * @param string $database
     * @return DocumentInterface[]|QueryResultInterface
     */
    public function findByDatabase(string $database)
    {
        $query = $this->createQuery();
        $query->matching($query->equals('db', $this->prepareDatabaseArgument($database)));

        return $this->convertQueryResult($query);
    }

    /**
     * Return all objects of the given Document database
     *
     * @param string $database
     * @return int
     */
    public function countByDatabase(string $database): int
    {
        $query = $this->createQuery();
        $query->matching($query->equals('db', $this->prepareDatabaseArgument($database)));

        return $query->count();
    }

    /**
     * Return the Document with the given ID in the given database
     *
     * @param string $database
     * @param string $id
     * @return DocumentInterface
     */
    public function findOneByDatabaseAndId(string $database, string $id): ?DocumentInterface
    {
        /** @var Query $query */
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
     *
     * @param string $database
     */
    public function removeAllFromDatabase(string $database): void
    {
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        foreach ($this->findByDatabase($database) as $document) {
            $this->remove($document);
        }
    }

    /**
     * @param Query               $query
     * @param ConstraintInterface $constraint
     * @return DocumentInterface|null
     */
    private function findOneByConstraint(
        Query $query,
        ConstraintInterface $constraint
    ): ?DocumentInterface {
        $query->matching(
            $constraint
        );

        $query->setLimit(1);
        if (!$this->useCustomDataMapping) {
            /** @var DocumentInterface $first */
            $first = $query->execute()->getFirst();

            return $first;
        }

        $result = $this->convertCollection($query->execute(true));
        if (!$result) {
            return null;
        } else {
            return reset($result);
        }
    }

    /**
     * Return a query for objects of this repository
     *
     * @return QueryInterface|Query
     */
    public function createQuery()
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
     * @param string $methodName The name of the magic method
     * @param string $arguments  The arguments of the magic method
     * @return mixed
     * @throws UnsupportedMethodException
     */
    public function __call($methodName, $arguments)
    {
        throw new UnsupportedMethodException(
            'The method "' . $methodName . '" is not supported by the repository.', 1233180480
        );
    }

    /**
     * Convert the query results into Documents
     *
     * @param array[] $collection
     * @return DocumentInterface[]
     */
    private function convertCollection(array $collection): array
    {
        return $this->dataMapper->map($this->objectType, $collection);
    }

    /**
     * Convert the query result into Documents
     *
     * @param QueryInterface $query
     * @return DocumentInterface[]|QueryResultInterface
     */
    private function convertQueryResult(QueryInterface $query)
    {
        $queryResult = $query->execute($this->useCustomDataMapping);
        if (!$this->useCustomDataMapping) {
            return $queryResult;
        } else {
            return $this->convertCollection($queryResult);
        }
    }

    /**
     * Invoked before a Document in the repository will be changed
     *
     * @param DocumentInterface $document
     */
    private function willChangeDocument($document)
    {
        // noop
    }

    /**
     * Invoked after a Document in the repository will be changed
     *
     * @param DocumentInterface $document
     */
    private function didChangeDocument($document)
    {
        // noop
    }

    /**
     * @param DocumentInterface $object
     * @return DocumentInterface
     */
    private function checkDocumentDatabase(DocumentInterface $object): DocumentInterface
    {
        if (!$object->getDb()) {
            throw new NoDatabaseSelectedException(
                'The given object has no database set',
                1389257938
            );
        }

        return $object;
    }

    private function prepareDatabaseArgument(?string $database): string
    {
        if (!$database) {
            throw new NoDatabaseSelectedException('No Document database has been selected', 1389258204);
        }
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        return $database;
    }

    /**
     * @param string $guid
     * @return array
     */
    private function splitGuid(string $guid): array
    {
        list($database, $id) = explode('/', $guid, 2);
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        return [$database, $id];
    }
}
