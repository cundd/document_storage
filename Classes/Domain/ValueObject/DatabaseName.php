<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\ValueObject;

use Cundd\DocumentStorage\Exception\InvalidDatabaseNameException;

/**
 * Information about a database as specified in Documents
 */
final readonly class DatabaseName
{
    private string $name;

    public function __construct(DatabaseName|string $name)
    {
        if (is_string($name)) {
            InvalidDatabaseNameException::assertValidDatabaseName($name);
        }
        $this->name = (string) $name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
