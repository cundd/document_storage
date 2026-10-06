<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Service;

use Cundd\DocumentStorage\Constants;
use Cundd\DocumentStorage\Gc\GcWhereExpression;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

use function implode;
use function sprintf;
use function time;

final class GcService
{
    private const TABLE_NAME = 'tx_documentstorage_domain_model_document';

    public function __construct(private readonly ConnectionPool $connectionPool)
    {
    }

    /**
     * Permanently delete the Documents marked as "deleted"
     *
     * @param int         $minAge Minimum age in seconds of Documents to delete
     * @param string|null $db     Name of the database to delete Documents from
     *
     * @return bool Return TRUE on success otherwise FALSE
     */
    public function removeDeletedDocuments(int $minAge, ?string $db = null): bool
    {
        $connection = $this->getConnection();
        $whereExpression = $this->buildWhere($minAge, $db);
        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            Constants::DOCUMENTS_TABLE_NAME,
            $whereExpression->sqlString
        );
        $stmt = $connection->prepare($sql);
        foreach ($whereExpression->parameters as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        return (int) $stmt->executeStatement() > 0;
    }

    /**
     * Fetch the number of Documents marked as "deleted"
     *
     * @param int         $minAge Minimum age in seconds of Documents to delete
     * @param string|null $db     Name of the database to delete Documents from
     */
    public function countDeletedDocuments(int $minAge, ?string $db = null): int
    {
        $connection = $this->getConnection();
        $whereExpression = $this->buildWhere($minAge, $db);
        $sql = sprintf(
            'SELECT count(*) FROM %s WHERE %s',
            self::TABLE_NAME,
            $whereExpression->sqlString
        );

        $stmt = $connection->prepare($sql);
        foreach ($whereExpression->parameters as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $result = $stmt->executeQuery();

        return (int) $result->fetchFirstColumn();
    }

    private function getConnection(): Connection
    {
        return $this->connectionPool->getConnectionForTable(self::TABLE_NAME);
    }

    private function buildWhere(int $minAge, ?string $db = null): GcWhereExpression
    {
        $parts = [];
        $parameters = [];

        $parts[] = 'deleted = :deleted';
        $parameters['deleted'] = 1;

        $parts[] = 'tstamp < :change_date';
        $parameters['change_date'] = time() - $minAge;
        if ($db) {
            $parts[] = 'db = :database';
            $parameters['database'] = $db;
        }

        return new GcWhereExpression(implode(' AND ', $parts), $parameters);
    }
}
