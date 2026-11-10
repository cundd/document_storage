<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Persistence\Repository;

use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Domain\Repository\DocumentRepositoryInterface;
use Cundd\DocumentStorage\Domain\ValueObject\DatabaseName;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * @template T of DocumentInterface
 *
 * @extends DocumentRepositoryInterface<T>
 *
 * @internal
 */
interface CoreDocumentRepositoryInterface extends DocumentRepositoryInterface
{
    /**
     * Return all objects of the given Document database
     */
    public function countByDatabase(DatabaseName $database): int;

    /**
     * Return all objects of the given Document database
     *
     * @return T[]|QueryResultInterface<int,T>
     */
    public function findByDatabase(DatabaseName $database): array|QueryResultInterface;

    /**
     * Remove all Documents from the given database
     */
    public function removeAllFromDatabase(DatabaseName $database): void;
}
