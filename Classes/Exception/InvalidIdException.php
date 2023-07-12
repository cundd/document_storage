<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Exception;

/**
 * Exception thrown if the given ID is invalid
 */
class InvalidIdException extends DomainException
{
    /**
     * @param string|int $id
     */
    public static function assertValidId($id)
    {
        if (!is_string($id) && !is_int($id)) {
            throw new static('ID must be either a string or integer value', 1389258925);
        }

        $cleanId = str_replace(['-', '_'], '', (string)$id);
        if (!trim($cleanId)) {
            throw new static('ID must must not be empty', 1389258924);
        }
        if (!ctype_alnum($cleanId)) {
            throw new static('ID must contain only alphanumeric characters, "-" and "_"', 1389258923);
        }
    }
}
