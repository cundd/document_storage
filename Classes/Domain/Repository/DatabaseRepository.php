<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Repository;

use Cundd\DocumentStorage\Constants;
use Cundd\DocumentStorage\Domain\Model\Database;
use DateTimeImmutable;
use Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Repository to fetch information about Databases (not the Documents inside)
 */
final class DatabaseRepository
{
    public function __construct(private readonly ConnectionPool $connectionPool)
    {
    }

    /**
     * @return Database[]
     */
    public function findAll(): array
    {
        $queryBuilder = $this->connectionPool
            ->getQueryBuilderForTable(Constants::DOCUMENTS_TABLE_NAME);
        $result = $queryBuilder->select('db as name', 'crdate as creationTime')
            ->from(Constants::DOCUMENTS_TABLE_NAME)
            ->orderBy('db', 'ASC')
            ->addOrderBy('crdate', 'ASC')
            ->groupBy('db')
            ->executeQuery();

        $databases = [];
        foreach ($result->fetchAllAssociative() as $row) {
            $name = $row['name'];
            try {
                $creationTime = new DateTimeImmutable('@' . $row['creationTime']);
            } catch (Exception $e) {
                $creationTime = null;
            }
            $databases[] = new Database($name, $creationTime);
        }

        return $databases;
    }
}
