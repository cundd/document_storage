<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Model;

use Cundd\DocumentStorage\Exception\InvalidDatabaseNameException;
use Cundd\DocumentStorage\Exception\InvalidDocumentException;
use Cundd\DocumentStorage\Exception\InvalidIdException;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use function is_null;
use function is_scalar;
use function is_string;

class Document extends AbstractEntity implements DocumentInterface
{
    /**
     * ID
     *
     * @var \string
     * @identity
     */
    protected $id;

    /**
     * Database
     *
     * @var \string
     * @identity
     */
    protected $db;

    /**
     * @var \bool
     */
    protected $deleted;

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
     * Document data
     *
     * @var \string
     */
    protected $dataProtected;

    /**
     * Unpacked Document content
     *
     * @var array
     */
    protected $_dataUnpacked = null;

    final public function getGuid(): ?string
    {
        $guid = $this->db . '/' . $this->id;

        return $guid !== '/' ? $guid : null;
    }

    final public function setId($id)
    {
        InvalidIdException::assertValidId($id);
        $this->id = (string)$id;
    }

    final public function getId(): ?string
    {
        $id = $this->id;
        if (is_null($id) || is_string($id)) {
            return $id;
        }
        if (is_scalar($id)) {
            return (string)$id;
        }
        throw new InvalidIdException('ID is not valid');
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
    final public function setDataProtected(string $content)
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

    final public function setDb(string $db)
    {
        InvalidDatabaseNameException::assertValidDatabaseName($db);
        $this->db = strtolower($db);
    }

    final public function getDb(): ?string
    {
        return $this->db;
    }

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

    public function valueForUndefinedKey(
        /** @noinspection PhpUnusedParameterInspection */
        string $key
    ) {
        return null;
    }

    public function setValueForKey(string $key, $value): DocumentInterface
    {
        if ($key === self::DATA_PROPERTY_NAME) {
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

    public function __get($name)
    {
        return $this->valueForKey((string)$name);
    }

    public function __isset($name)
    {
        return (bool)$this->valueForKey((string)$name);
    }

    /**
     * Packs the Document content
     *
     * @return DocumentInterface
     */
    private function _packContent()
    {
        $this->dataProtected = json_encode($this->_dataUnpacked);

        return $this;
    }
}
