<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Persistence\Repository;

use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Exception\InvalidDatabaseNameException;
use Cundd\DocumentStorage\Exception\InvalidDocumentDatabaseException;
use Cundd\DocumentStorage\Exception\NoDatabaseSelectedException;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
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
    private $database = '';

    /**
     * Constructs a new Repository
     *
     * @param ObjectManagerInterface               $objectManager
     * @param string                               $database Identifier of the managed database
     * @param CoreDocumentRepositoryInterface|null $baseRepository
     * @param string                               $objectType
     */
    public function __construct(
        ?ObjectManagerInterface $objectManager,
        string $database,
        ?CoreDocumentRepositoryInterface $baseRepository = null,
        string $objectType = Document::class
    ) {
        InvalidDatabaseNameException::assertValidDatabaseName($database);
        parent::__construct($objectManager, $baseRepository, $objectType);
        $this->database = $database;
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
