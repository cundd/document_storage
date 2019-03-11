<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Repository;

use Cundd\DocumentStorage\Domain\Exception\InvalidDatabaseNameException;
use Cundd\DocumentStorage\Domain\Exception\NoDatabaseSelectedException;
use Cundd\DocumentStorage\Domain\Model\Document;
use Doctrine\DBAL\Driver\Statement;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class FreeDocumentRepository extends AbstractDocumentRepository
{
    /**
     * Return all objects of the given Document database
     *
     * @param string $database
     * @return Document[]|QueryResultInterface
     */
    public function findByDatabase(string $database)
    {
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        return $this->baseRepository->findByDatabase($database);
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

        return $this->baseRepository->removeAllFromDatabase($database);
    }

    public function findAll(string $database = null)
    {
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        return $this->baseRepository->findByDatabase($database);
    }

    public function countAll(string $database = null)
    {
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        return $this->baseRepository->countAll($database);
    }

    public function removeAll(string $database = null)
    {
        InvalidDatabaseNameException::assertValidDatabaseName($database);

        return $this->baseRepository->removeAllFromDatabase($database);
    }

    public function findOneById(string $id)
    {
        return $this->baseRepository->findOneById($id);
    }

    public function findWithProperties(array $properties, int $limit = PHP_INT_MAX): array
    {
        if (isset($properties['db'])) {
            $database = $properties['db'];
        } elseif (isset($properties['database'])) {
            $database = $properties['database'];
        } else {
            throw new NoDatabaseSelectedException('Missing key "database"');
        }

        return $this->baseRepository->findWithProperties(
            array_merge(
                $properties,
                ['db' => $database]
            ),
            $limit
        );
    }

    /**
     * @param Document $object
     * @return Document
     */
    protected function checkDocumentDatabase(Document $object): Document
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
