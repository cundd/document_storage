<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Exception;

/**
 * Exception thrown if the given database name is invalid
 */
class InvalidDatabaseNameException extends DomainException
{
    public static function assertValidDatabaseName(string $database)
    {
        if ('' === trim($database)) {
            throw new InvalidDatabaseNameException('Database name must not be empty', 1551889307);
        }
        if (!ctype_alnum($database)) {
            throw new InvalidDatabaseNameException('The given database name contains invalid characters', 1389258923);
        }
        if (strtolower($database) !== $database) {
            throw new InvalidDatabaseNameException('The given database name must be lowercase', 1389348390);
        }
    }
}
