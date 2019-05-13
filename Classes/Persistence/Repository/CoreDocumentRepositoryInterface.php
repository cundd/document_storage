<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Persistence\Repository;

use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Domain\Repository\DocumentRepositoryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

interface CoreDocumentRepositoryInterface extends DocumentRepositoryInterface
{
    /**
     * Return all objects of the given Document database
     *
     * @param string $database
     * @return int
     */
    public function countByDatabase(string $database): int;

    /**
     * Return all objects of the given Document database
     *
     * @param string $database
     * @return DocumentInterface[]|QueryResultInterface
     */
    public function findByDatabase(string $database);

    /**
     * Remove all Documents from the given database
     *
     * @param string $database
     */
    public function removeAllFromDatabase(string $database): void;
}
