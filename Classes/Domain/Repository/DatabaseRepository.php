<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Repository;


use Cundd\DocumentStorage\Domain\Model\Database;
use Cundd\DocumentStorage\Domain\Model\Document;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

class DatabaseRepository
{
    private $objectManager;
    private $connectionPool;

    /**
     * DatabaseRepository constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param ConnectionPool         $connectionPool
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConnectionPool $connectionPool
    ) {
        $this->connectionPool = $connectionPool;
        $this->objectManager = $objectManager;
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
                $creationTime = new \DateTimeImmutable('@' . $row['creationTime']);
            } catch (\Exception $e) {
                $creationTime = null;
            }
            $databases[] = new Database($name, $creationTime);
        }

        return $databases;
    }

    /**
     * @return string
     */
    private function getTable(): string
    {
        static $table = null;
        if (null === $table) {
            $table = $this->objectManager->get(DataMapper::class)->getDataMap(Document::class)->getTableName();
        }

        return $table;
    }
}