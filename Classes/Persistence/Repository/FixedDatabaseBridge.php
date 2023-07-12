<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Persistence\Repository;

use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Exception\InvalidDatabaseNameException;
use Cundd\DocumentStorage\Exception\InvalidDocumentDatabaseException;
use Cundd\DocumentStorage\Exception\NoDatabaseSelectedException;

use function array_merge;
use function sprintf;

use const PHP_INT_MAX;

abstract class FixedDatabaseBridge extends AbstractBridge
{
    /**
     * Name of the database managed by this repository
     *
     * @var string
     */
    private string $database = '';

    /**
     * Constructs a new Repository
     *
     * @param string $database Identifier of the managed database
     * @param CoreDocumentRepositoryInterface $baseRepository
     * @param string $objectType
     */
    public function __construct(
        string $database,
        CoreDocumentRepositoryInterface $baseRepository,
        string $objectType = Document::class
    ) {
        InvalidDatabaseNameException::assertValidDatabaseName($database);
        parent::__construct($baseRepository, $objectType);
        $this->database = $database;
    }

    /**
     * @param string $uid The identifier of the object to find
     * @return DocumentInterface|null The matching object if found, otherwise NULL
     * @see findByIdentifier()
     */
    public function findByUid($uid)
    {
        return $this->findByIdentifier($uid);
    }

    /**
     * Find an object matching the given ID
     *
     * @param string $identifier The identifier of the object to find
     * @return DocumentInterface|null The matching object if found, otherwise NULL
     */
    public function findByIdentifier($identifier)
    {
        return $this->findById((string)$identifier);
    }

    public function findAll()
    {
        return $this->baseRepository->findByDatabase($this->getDatabase());
    }

    public function removeAll()
    {
        $this->baseRepository->removeAllFromDatabase($this->getDatabase());
    }

    public function countAll()
    {
        return $this->baseRepository->countByDatabase($this->getDatabase());
    }

    /**
     * Return the Document with the given ID
     *
     * @param string $id
     * @return DocumentInterface
     * @see findById()
     */
    public function findOneById(string $id)
    {
        return $this->findById($id);
    }

    /**
     * Return the Document with the given ID
     *
     * @param string $id
     * @return DocumentInterface
     */
    public function findById(string $id)
    {
        return $this->baseRepository->findOneByDatabaseAndId($this->getDatabase(), $id);
    }

    public function findWithProperties(array $properties, int $limit = PHP_INT_MAX): iterable
    {
        return $this->baseRepository->findWithProperties(
            array_merge(
                $properties,
                ['db' => $this->getDatabase()]
            ),
            $limit
        );
    }

    /**
     * Return the name of the database managed by this repository
     *
     * @return string
     */
    protected function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @param DocumentInterface|object $object
     * @return DocumentInterface
     */
    protected function checkDocumentDatabase(DocumentInterface $object): DocumentInterface
    {
        $currentDatabase = $this->getDatabase();
        if (!$object->getDb()) {
            if (!$currentDatabase) {
                throw new NoDatabaseSelectedException(
                    'The given Document and the repository have no database set',
                    1389257938
                );
            }
            $object->setDb($currentDatabase);
        } elseif ($object->getDb() !== $currentDatabase) {
            throw new InvalidDocumentDatabaseException(
                sprintf(
                    'Document does not belong to this repository. The Document\'s database "%s" does not match the repository\'s database "%s"',
                    $object->getDb(),
                    $currentDatabase
                )
            );
        }

        return $object;
    }
}
