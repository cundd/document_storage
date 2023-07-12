<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Service;

use Cundd\DocumentStorage\Gc\GcWhereExpression;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

use function implode;
use function sprintf;
use function time;

class GcService
{
    private const TABLE_NAME = 'tx_documentstorage_domain_model_document';

    public function __construct(readonly private ConnectionPool $connectionPool)
    {
    }

    /**
     * Permanently delete the Documents marked as "deleted"
     *
     * @param int         $minAge Minimum age in seconds of Documents to delete
     * @param string|null $db     Name of the database to delete Documents from
     * @return bool Return TRUE on success otherwise FALSE
     */
    public function removeDeletedDocuments(int $minAge, ?string $db = null): bool
    {
        $connection = $this->getConnection();
        $whereExpression = $this->buildWhere($minAge, $db);
        $sql = sprintf("DELETE FROM %s WHERE $whereExpression", self::TABLE_NAME);
        $stmt = $connection->prepare($sql);

        return $stmt->executeStatement($whereExpression->getParameters()) > 0;
    }

    /**
     * Fetch the number of Documents marked as "deleted"
     *
     * @param int         $minAge Minimum age in seconds of Documents to delete
     * @param string|null $db     Name of the database to delete Documents from
     * @return int
     */
    public function countDeletedDocuments(int $minAge, ?string $db = null): int
    {
        $connection = $this->getConnection();
        $whereExpression = $this->buildWhere($minAge, $db);
        $sql = sprintf("SELECT count(*) FROM %s WHERE $whereExpression", self::TABLE_NAME);

        $stmt = $connection->prepare($sql);
        $result = $stmt->executeQuery($whereExpression->getParameters());

        return (int)$result->fetchFirstColumn();
    }

    private function getConnection(): Connection
    {
        return $this->connectionPool->getConnectionForTable(self::TABLE_NAME);
    }

    private function buildWhere(int $minAge, ?string $db = null): GcWhereExpression
    {
        $parts = [];
        $parameters = [];

        $parts[] = 'deleted = ?';
        $parameters[] = 1;

        $parts[] = 'tstamp < ?';
        $parameters[] = time() - $minAge;
        if ($db) {
            $parts[] = 'db = ?';
            $parameters[] = $db;
        }

        return new GcWhereExpression(implode(' AND ', $parts), $parameters);
    }
}
