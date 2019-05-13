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
     *
     * @return string
     */
    public function getGuid(): ?string;

    /**
     * Set the Document's ID
     *
     * @param string|int $id
     */
    public function setId($id);

    /**
     * Return the Document's ID
     *
     * @return string
     */
    public function getId(): ?string;

    /**
     * @return bool
     */
    public function isDeleted(): bool;

    /**
     * @param bool $deleted
     */
    public function setDeleted(bool $deleted): void;

    /**
     * Set the Document's database
     *
     * @param string $db
     * @throws InvalidDatabaseNameException if the given database name is not valid
     */
    public function setDb(string $db);

    /**
     * Return the Document's database
     *
     * @return string
     */
    public function getDb(): ?string;

    /**
     * Return the value for the given key
     *
     * @param string $key
     * @return mixed
     */
    public function valueForKey(string $key);

    /**
     * Return the value for the given key path (i.e. "foo.bar")
     *
     * @param string $keyPath
     * @param mixed  $default
     * @return mixed
     */
    public function valueForKeyPath(string $keyPath, $default = null);

    /**
     * Invoked if a retrieved key is not defined
     *
     * @param string $key
     * @return null
     */
    public function valueForUndefinedKey(string $key);

    /**
     * Set the value for the given key
     *
     * @param string $key
     * @param mixed  $value
     * @return DocumentInterface
     */
    public function setValueForKey(string $key, $value): DocumentInterface;
}