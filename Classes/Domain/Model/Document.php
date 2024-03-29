<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Model;

use Cundd\DocumentStorage\Constants;
use Cundd\DocumentStorage\Exception\InvalidDatabaseNameException;
use Cundd\DocumentStorage\Exception\InvalidIdException;
use Cundd\DocumentStorage\Persistence\JsonSerializer;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

use function is_null;

class Document extends AbstractEntity implements DocumentInterface
{
    /**
     * ID
     *
     * @var string
     */
    protected string $id = '';

    /**
     * Database
     *
     * @var string
     */
    protected string $db = '';

    protected bool $deleted = false;

    /**
     * Creation time
     *
     * @var int
     */
    protected int $creationTime = 0;

    /**
     * Modification time
     *
     * @var int
     */
    protected int $modificationTime = 0;

    /**
     * Document data
     *
     * @var string|null
     */
    protected ?string $dataProtected = null;

    /**
     * Unpacked Document content
     *
     * @var array|null
     */
    protected ?array $_dataUnpacked = null;

    final public function getGuid(): ?string
    {
        $guid = $this->db . '/' . $this->id;

        return $guid !== '/' ? $guid : null;
    }

    final public function setId(int|string $id): void
    {
        InvalidIdException::assertValidId($id);
        $this->id = (string)$id;
    }

    final public function getId(): ?string
    {
        return $this->id;
    }

    final public function isDeleted(): bool
    {
        return (bool)$this->deleted;
    }

    final public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    /**
     * Set the Document's data
     *
     * @param string $content
     */
    final public function setDataProtected(string $content): void
    {
        $this->dataProtected = $content;
        $this->_dataUnpacked = null;
    }

    /**
     * Return the Document's data
     *
     * @return string|null
     */
    final public function getDataProtected(): ?string
    {
        return $this->dataProtected;
    }

    final public function setDb(string $db): void
    {
        InvalidDatabaseNameException::assertValidDatabaseName($db);
        $this->db = strtolower($db);
    }

    final public function getDb(): ?string
    {
        return $this->db;
    }

    public function valueForKey(string $key): mixed
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

    public function valueForKeyPath(string $keyPath, mixed $default = null): mixed
    {
        if (!str_contains($keyPath, '.')) {
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

    public function valueForUndefinedKey(string $key)
    {
        return null;
    }

    public function setValueForKey(string $key, $value): DocumentInterface
    {
        if ($key === Constants::DATA_PROPERTY_NAME) {
            $this->dataProtected = $value;
            $this->_dataUnpacked = null;

            return $this;
        }
        $setter = 'set' . ucfirst($key);
        if (method_exists($this, $setter)) {
            $this->$setter($value);

            return $this;
        } elseif (property_exists($this, $key)) {
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
     * Return the unpacked Document data
     *
     * @return array|mixed
     */
    final public function getUnpackedData()
    {
        if (!$this->_dataUnpacked) {
            $jsonSerializer = new JsonSerializer();
            $this->_dataUnpacked = $jsonSerializer->deserialize($this->dataProtected);
        }

        return $this->_dataUnpacked;
    }

    public function __get($name)
    {
        return $this->valueForKey((string)$name);
    }

    public function __isset($name)
    {
        return (bool)$this->valueForKey((string)$name);
    }

    /**
     * Pack the Document content
     */
    private function _packContent(): void
    {
        $jsonSerializer = new JsonSerializer();
        $this->dataProtected = $jsonSerializer->serialize($this->_dataUnpacked);
    }
}
