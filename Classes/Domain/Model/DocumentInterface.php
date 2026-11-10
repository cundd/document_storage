<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Model;

use Cundd\DocumentStorage\Exception\InvalidDatabaseNameException;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

/**
 * Document is a flexible, schema-less object
 */
interface DocumentInterface extends DomainObjectInterface
{
    /**
     * Return the Documents global unique identifier
     */
    public function getGuid(): ?string;

    /**
     * Set the Document's ID
     *
     * @param non-empty-string $id
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
     * @param non-empty-string $db
     *
     * @throws InvalidDatabaseNameException if the given database name is not valid
     */
    public function setDb(string $db): void;

    /**
     * Return the Document's database
     *
     * @return non-empty-string
     */
    public function getDb(): ?string;

    /**
     * Return if the Document's database is set
     */
    public function hasDb(): bool;

    /**
     * Return the value for the given key
     *
     * @param non-empty-string $key
     */
    public function valueForKey(string $key): mixed;

    /**
     * Return the value for the given key path (i.e. "foo.bar")
     *
     * @param non-empty-string $keyPath
     */
    public function valueForKeyPath(string $keyPath, mixed $default = null): mixed;

    /**
     * Invoked if a retrieved key is not defined
     *
     * @param non-empty-string $key
     */
    public function valueForUndefinedKey(string $key): mixed;

    /**
     * Set the value for the given key
     *
     * @param non-empty-string $key
     */
    public function setValueForKey(string $key, mixed $value): DocumentInterface;
}
