<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Repository;

use Cundd\DocumentStorage\Domain\Model\Database;
use Cundd\DocumentStorage\Domain\Model\Document;
use DateTimeImmutable;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * Repository to fetch information about Databases (not the Documents inside)
 */
class DatabaseRepository
{
    public function __construct(readonly private ConnectionPool $connectionPool)
    {
    }

    /**
     * @return Database[]
     */
    public function findAll(): array
    {
        $table = $this->getTable();
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $result = $queryBuilder->select('db as name', 'crdate as creationTime')
            ->from($table)
            ->orderBy('db', 'ASC')
            ->addOrderBy('crdate', 'ASC')
            ->groupBy('db')
            ->execute();

        $databases = [];
        foreach ($result->fetchAll() as $row) {
            $name = $row['name'];
            try {
                $creationTime = new DateTimeImmutable('@' . $row['creationTime']);
            } catch (\Exception $e) {
                $creationTime = null;
            }
            $databases[] = new Database($name, $creationTime);
        }

        return $databases;
    }

    private function getTable(): string
    {
        static $table = null;
        if (null === $table) {
            try {
                $table = GeneralUtility::makeInstance(DataMapper::class)->getDataMap(Document::class)->getTableName();
            } catch (Exception $e) {
            }
        }

        return $table;
    }
}
