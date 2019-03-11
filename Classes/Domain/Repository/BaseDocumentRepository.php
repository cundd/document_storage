<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Repository;

use Cundd\DocumentStorage\Domain\Exception\InvalidDatabaseNameException;
use Cundd\DocumentStorage\Domain\Exception\NoDatabaseSelectedException;
use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Persistence\DataMapper;
use Doctrine\DBAL\Driver\Statement;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class BaseDocumentRepository extends Repository implements DocumentRepositoryInterface
{
    /**
     * Defines if query results should be retrieved raw and converted by convertCollection()
     *
     * @var bool
     */
    private $useCustomDataMapping = false;

    /**
     * @var ConnectionPool
     */
    private $connectionPool;

    /**
     * @var DataMapper|null
     */
    private $dataMapper;

    /**
     * Concrete Repository implementation which will be used by `DocumentRepository` and `FreeDocumentRepository`
     *
     * @param ObjectManagerInterface $objectManager
     * @param ConnectionPool|null    $connectionPool
     * @param DataMapper|null        $dataMapper
     */
    private function __construct(
        ObjectManagerInterface $objectManager,
        ?ConnectionPool $connectionPool = null,
        ?DataMapper $dataMapper = null
    ) {
        parent::__construct($objectManager);
        $this->injectPersistenceManager($objectManager->get(PersistenceManagerInterface::class));
        $this->connectionPool = $connectionPool ?? $objectManager->get(ConnectionPool::class);
        $this->dataMapper = $dataMapper ?? $objectManager->get(DataMapper::class);
        $this->objectType = Document::class;
    }

    public static function build(
        ObjectManagerInterface $objectManager,
        ?ConnectionPool $connectionPool = null
    ): BaseDocumentRepository {
        return new static($objectManager, $connectionPool);
    }

    /**
     * Add an object to this repository
     *
     * @param Document $object The object to add
     * @throws NoDatabaseSelectedException if the given object and the repository have no database set
     * @return void
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
     * @param Document $object The object to remove
     * @throws NoDatabaseSelectedException if the given object and the repository have no database set
     * @return void
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
     * @param Document $modifiedObject The modified object
     * @throws NoDatabaseSelectedException if the given object and the repository have no database set
     * @return void
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
     * @return Document[]|QueryResultInterface
     */
    public function findAll(string $database = null)
    {
        return $this->findByDatabase($this->prepareDatabaseArgument($database));
    }

    public function findByGuid(string $guid): ?Document
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

    public function countAll(string $database = null)
    {
        $query = $this->createQuery();
        $query->matching($query->equals('db', $this->prepareDatabaseArgument($database)));

        return $query->execute()->count();
    }

    public function findWithProperties(array $properties, int $limit = PHP_INT_MAX): array
    {
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
        if (!empty($constraintsCollection)) {
            $query->matching($query->logicalAnd($constraintsCollection));
        }

        // Get the objects
        $resultCollection = $this->convertQueryResult($query);

        // Filter using the remaining properties
        $filteredResultCollection = [];
        $filteredResultCollectionCount = 0;

        /*
         * Loop through each found Document and check each of the properties
         * that were not filtered in the query
         */
        foreach ($resultCollection as $currentDocument) {
            if ($this->documentMatchesProperties($properties, $currentDocument)) {
                $filteredResultCollection[] = $currentDocument;

                $filteredResultCollectionCount += 1;
                if ($filteredResultCollectionCount >= $limit) {
                    break;
                }
            }
        }

        return $filteredResultCollection;
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
     * @return Document[]|QueryResultInterface
     */
    public function findByDatabase(string $database)
    {
        $query = $this->createQuery();
        $query->matching($query->equals('db', $this->prepareDatabaseArgument($database)));

        return $this->convertQueryResult($query);
    }

    /**
     * Return the Document with the given ID in the given database
     *
     * @param string $database
     * @param string $id
     * @return Document
     */
    public function findOneByDatabaseAndId(string $database, string $id): ?Document
    {
        /** @var Query $query */
        $query = $this->createQuery();
        InvalidDatabaseNameException::assertValidDatabaseName($database);
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
     * @return Statement
     */
    public function removeAllFromDatabase(string $database): Statement
    {
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        /** @noinspection SqlResolve */
        return $this->getDatabaseConnection()->executeQuery(
            sprintf('UPDATE %s SET deleted=2 WHERE db=?', $this->getRawDatabaseTableName()),
            [$database]
        );
    }

    /**
     * @param Query               $query
     * @param ConstraintInterface $constraint
     * @return Document|null
     */
    private function findOneByConstraint(
        Query $query,
        ConstraintInterface $constraint
    ): ?Document {
        $query->matching(
            $constraint
        );

        $query->setLimit(1);
        if (!$this->useCustomDataMapping) {
            /** @var Document $first */
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
     * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface|Query
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
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException
     * @return mixed
     */
    public function __call($methodName, $arguments)
    {
        throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException(
            'The method "' . $methodName . '" is not supported by the repository.', 1233180480
        );
    }

    /**
     * Convert the query results into Documents
     *
     * @param array[] $collection
     * @return Document[]
     */
    private function convertCollection(array $collection): array
    {
        return $this->dataMapper->map($this->objectType, $collection);
    }

    /**
     * Convert the query result into Documents
     *
     * @param QueryInterface $query
     * @return Document[]|QueryResultInterface
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
     * @param Document $document
     */
    private function willChangeDocument($document)
    {
    }

    /**
     * Invoked after a Document in the repository will be changed
     *
     * @param Document $document
     */
    private function didChangeDocument($document)
    {
    }

    /**
     * @return string
     */
    private function getRawDatabaseTableName(): string
    {
        return 'tx_documentstorage_domain_model_document';
    }

    /**
     * @param Document $object
     * @return Document
     */
    private function checkDocumentDatabase(Document $object): Document
    {
        if (!$object->getDb()) {
            throw new NoDatabaseSelectedException(
                'The given object has no database set',
                1389257938
            );
        }

        return $object;
    }

    /**
     * @return \TYPO3\CMS\Core\Database\Connection
     */
    private function getDatabaseConnection()
    {
        return $this->connectionPool->getConnectionForTable($this->getRawDatabaseTableName());
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

    /**
     * @param array    $properties
     * @param Document $currentDocument
     * @return bool
     */
    private function documentMatchesProperties(array $properties, Document $currentDocument): bool
    {
        $currentDocumentData = $currentDocument->getUnpackedData();
        foreach ($properties as $propertyKey => $propertyValue) {
            if ($propertyValue !== ObjectAccess::getPropertyPath($currentDocumentData, $propertyKey)) {
                return false;
            }
        }

        return true;
    }
}
