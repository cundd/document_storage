<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Exception;

use function is_string;
use function str_replace;

/**
 * Exception thrown if the given database name is invalid
 */
class InvalidDatabaseNameException extends DomainException
{
    public static function assertValidDatabaseName($database)
    {
        if (!is_string($database)) {
            throw new InvalidDatabaseNameException('Database name must be a string', 1557387584);
        }

        $cleanName = str_replace(['-', '_'], '', $database);
        if ('' === trim($cleanName)) {
            throw new InvalidDatabaseNameException('Database name must not be empty', 1551889307);
        }
        if (!ctype_alnum($cleanName)) {
            throw new InvalidDatabaseNameException(
                'Database name must contain only alphanumeric characters, "-" and "_"', 1389258923
            );
        }
        if (strtolower($database) !== $database) {
            throw new InvalidDatabaseNameException('Database name must be lowercase', 1389348390);
        }
    }
}
