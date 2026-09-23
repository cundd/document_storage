<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Model;

use Cundd\DocumentStorage\Exception\InvalidDatabaseNameException;

/**
 * Document is a flexible, schema-less object
 */
interface DocumentInterface
{
    /**
     * Return the Documents global unique identifier
     */
    public function getGuid(): ?string;

    /**
     * Set the Document's ID
     */
    public function setId(int|string $id): void;

    /**
     * Return the Document's ID
     */
    public function getId(): ?string;

    public function isDeleted(): bool;

    public function setDeleted(bool $deleted): void;

    /**
     * Set the Document's database
     *
     * @throws InvalidDatabaseNameException if the given database name is not valid
     */
    public function setDb(string $db);

    /**
     * Return the Document's database
     */
    public function getDb(): ?string;

    /**
     * Return the value for the given key
     */
    public function valueForKey(string $key): mixed;

    /**
     * Return the value for the given key path (i.e. "foo.bar")
     */
    public function valueForKeyPath(string $keyPath, mixed $default = null): mixed;

    /**
     * Invoked if a retrieved key is not defined
     *
     * @return null
     */
    public function valueForUndefinedKey(string $key);

    /**
     * Set the value for the given key
     */
    public function setValueForKey(string $key, $value): DocumentInterface;
}
