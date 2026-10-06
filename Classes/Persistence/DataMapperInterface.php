<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Persistence;

use Cundd\DocumentStorage\Domain\Model\DocumentInterface;

/**
 * Data Mapper allows to create and populate Documents with data
 */
interface DataMapperInterface
{
    /**
     * Map the given rows on objects
     *
     * @template T of DocumentInterface
     *
     * @param class-string<T>           $className The name of the class
     * @param list<array<string,mixed>> $rows      An array of arrays with field_name => value pairs
     *
     * @return list<T> An array of objects of the given class
     */
    public function map(string $className, array $rows): array;

    /**
     * Map a single row on an object of the given class
     *
     * @template T of DocumentInterface
     *
     * @param class-string<T>     $className The name of the target class
     * @param array<string,mixed> $row       A single array with field_name => value pairs
     *
     * @return T
     */
    public function mapSingleRow(
        string $className,
        array $row,
    ): DocumentInterface;
}
