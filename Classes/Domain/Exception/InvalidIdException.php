<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Exception;

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

        if (!ctype_alnum(str_replace(['-', '_'], '', $id))) {
            throw new static('ID must contain only alphanumeric characters, "-" and "_"', 1389258923);
        }
    }
}
