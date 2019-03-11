<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Repository;

use Cundd\DocumentStorage\Domain\Exception\InvalidDatabaseNameException;
use Cundd\DocumentStorage\Domain\Exception\NoDatabaseSelectedException;
use Cundd\DocumentStorage\Domain\Model\Document;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 */
class DocumentRepository extends AbstractDocumentRepository
{
    /**
     * Currently selected database
     *
     * @var string
     */
    private $database = '';

    /**
     * Constructs a new Repository
     *
     * @param ObjectManagerInterface      $objectManager
     * @param string                      $database
     * @param BaseDocumentRepository|null $baseRepository
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        string $database,
        ?BaseDocumentRepository $baseRepository = null
    ) {
        InvalidDatabaseNameException::assertValidDatabaseName($database);
        parent::__construct($objectManager, $baseRepository);
        $this->database = $database;
    }

    public function findAll()
    {
        return $this->baseRepository->findByDatabase($this->getDatabase());
    }

    public function removeAll()
    {
        return $this->baseRepository->removeAllFromDatabase($this->getDatabase());
    }

    public function countAll()
    {
        return $this->baseRepository->countAll($this->getDatabase());
    }

    public function findOneById(string $id)
    {
        return $this->baseRepository->findOneByDatabaseAndId($this->database, $id);
    }

    public function findWithProperties(array $properties, int $limit = PHP_INT_MAX): array
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
     * @return string
     */
    private function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @param Document|object $object
     * @return Document
     */
    protected function checkDocumentDatabase(Document $object): Document
    {
        if (!$object->getDb()) {
            $currentDatabase = $this->getDatabase();
            if (!$currentDatabase) {
                throw new NoDatabaseSelectedException(
                    'The given object and the repository have no database set',
                    1389257938
                );
            }
            $object->setDb($currentDatabase);
        }

        return $object;
    }
}
