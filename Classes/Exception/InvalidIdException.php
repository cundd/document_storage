<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Exception;

/**
 * Exception thrown if the given ID is invalid
 */
final class InvalidIdException extends DomainException
{
    /**
     * @phpstan-assert non-empty-string|int<0,max> $id
     */
    public static function assertValidId(string|int $id): void
    {
        $cleanId = str_replace(['-', '_'], '', (string) $id);
        if (!trim($cleanId)) {
            throw new static('ID must must not be empty', 1389258924);
        }
        if (!ctype_alnum($cleanId)) {
            throw new static('ID must contain only alphanumeric characters, "-" and "_"', 1389258923);
        }
    }
}
