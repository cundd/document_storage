<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Model;

use Cundd\DocumentStorage\Domain\Exception\InvalidDatabaseNameException;
use Cundd\DocumentStorage\Domain\Exception\InvalidDocumentException;
use Cundd\DocumentStorage\Domain\Exception\InvalidIdException;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Document
 *
 * A Document is a flexible, schema-less object
 */
class Document extends AbstractEntity implements \ArrayAccess
{
    /**
     * Name of the property that holds the data
     */
    public const DATA_PROPERTY_NAME = 'dataProtected';

    /**
     * ID
     *
     * @var \string
     * @validate NotEmpty
     * @identity
     */
    protected $id;

    /**
     * Database
     *
     * @var \string
     * @validate NotEmpty
     * @identity
     */
    protected $db;

    /**
     * @var \bool
     */
    protected $deleted;

    /**
     * Document data
     *
     * @var \string
     * @validate NotEmpty
     */
    protected $dataProtected;

    /**
     * Creation time
     *
     * @var int
     */
    protected $creationTime;

    /**
     * Modification time
     *
     * @var int
     */
    protected $modificationTime;

    /**
     * Unpacked Document content
     *
     * @var array
     */
    protected $_dataUnpacked = null;

    /**
     * Returns the Documents global unique identifier
     *
     * @return string
     */
    final public function getGuid(): ?string
    {
        $guid = $this->db . '/' . $this->id;

        return $guid !== '/' ? $guid : null;
    }

    /**
     * Sets the Document's ID
     *
     * @param string $id
     */
    final public function setId(string $id)
    {
        InvalidIdException::assertValidId($id);
        $this->id = $id;
    }

    /**
     * Returns the Document's ID
     *
     * @return string
     */
    final public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    final public function isDeleted(): bool
    {
        return (bool)$this->deleted;
    }

    /**
     * @param bool $deleted
     */
    final public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }


    /**
     * Sets the Document's data
     *
     * @param string $content
     */
    final public function setDataProtected(string $content)
    {
        $this->dataProtected = $content;
        $this->_dataUnpacked = null;
    }

    /**
     * Returns the Document's data
     *
     * @return string|null
     */
    final public function getDataProtected(): ?string
    {
        return $this->dataProtected;
    }

    /**
     * Sets the Document's database
     *
     * @param string $db
     * @throws InvalidDatabaseNameException if the given database name is not valid
     */
    final public function setDb(string $db)
    {
        InvalidDatabaseNameException::assertValidDatabaseName($db);
        $this->db = strtolower($db);
    }

    /**
     * Returns the Document's database
     *
     * @return string
     */
    final public function getDb(): ?string
    {
        return $this->db;
    }

    /**
     * Returns the value for the given key
     *
     * @param string $key
     * @return mixed
     */
    public function valueForKey(string $key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        }

        $unpackedContent = $this->getUnpackedData();
        if (isset($unpackedContent[$key])) {
            return $unpackedContent[$key];
        }

        return $this->valueForUndefinedKey($key);
    }

    /**
     * Returns the value for the given key path (i.e. "foo.bar")
     *
     * @param string $keyPath
     * @param mixed  $default
     * @return mixed
     */
    public function valueForKeyPath(string $keyPath, $default = null)
    {
        if (strpos($keyPath, '.') === false) {
            return $this->valueForKey($keyPath);
        }

        $result = array_reduce(
            explode('.', $keyPath),
            function ($carry, string $key) {
                return is_array($carry) && isset($carry[$key]) ? $carry[$key] : null;
            },
            $this->getUnpackedData()
        );

        return is_null($result) ? $default : $result;
    }

    /**
     * Invoked if a retrieved key is not defined
     *
     * @param string $key
     * @return null
     */
    public function valueForUndefinedKey(
        /** @noinspection PhpUnusedParameterInspection */
        string $key
    ) {
        return null;
    }

    /**
     * Sets the value for the given key
     *
     * @param string $key
     * @param mixed  $value
     * @return Document
     */
    public function setValueForKey(string $key, $value): self
    {
        if ($key === self::DATA_PROPERTY_NAME) {
            $this->dataProtected = $value;
            $this->_dataUnpacked = null;

            return $this;
        }
        if (property_exists($this, $key)) {
            $this->$key = $value;

            return $this;
        }

        $unpackedContent = $this->getUnpackedData();
        $unpackedContent[$key] = $value;

        unset($this->_dataUnpacked);
        $this->_dataUnpacked = $unpackedContent;
        $this->_packContent();

        return $this;
    }

    /**
     * Returns the unpacked Document data
     *
     * @return array|mixed
     */
    final public function getUnpackedData()
    {
        if (!$this->_dataUnpacked) {
            if ($this->dataProtected && strtolower($this->dataProtected) !== 'null') {
                $this->_dataUnpacked = json_decode($this->dataProtected, true);

                if ($this->_dataUnpacked === null) {
                    throw new InvalidDocumentException(
                        'Invalid JSON data: ' . json_last_error_msg(),
                        json_last_error()
                    );
                }
            }
        }

        return $this->_dataUnpacked;
    }

    public function offsetExists($offset)
    {
        return (bool)$this->valueForKey((string)$offset);
    }

    public function offsetGet($offset)
    {
        return $this->valueForKey((string)$offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->setValueForKey((string)$offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->setValueForKey((string)$offset, null);
    }

    public function __get($name)
    {
        return $this->valueForKey((string)$name);
    }

    public function __isset($name)
    {
        return $this->offsetExists((string)$name);
    }

    /**
     * Packs the Document content
     *
     * @return $this
     */
    private function _packContent()
    {
        $this->dataProtected = json_encode($this->_dataUnpacked);

        return $this;
    }
}
