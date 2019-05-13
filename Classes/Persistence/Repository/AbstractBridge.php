<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Persistence\Repository;

use BadFunctionCallException;
use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Domain\Repository\DocumentRepositoryInterface;
use InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use function is_string;

/**
 * The Bridge builds the connection between the various Document repository implementations and the concrete Core
 * Document Repository
 */
abstract class AbstractBridge implements DocumentRepositoryInterface
{
    /**
     * @var CoreDocumentRepository
     */
    protected $baseRepository;

    /**
     * Construct a new Document Repository
     *
     * @param ObjectManagerInterface               $objectManager
     * @param CoreDocumentRepositoryInterface|null $baseRepository
     * @param string                               $objectType
     */
    public function __construct(
        ?ObjectManagerInterface $objectManager = null,
        ?CoreDocumentRepositoryInterface $baseRepository = null,
        string $objectType = Document::class
    ) {
        if ($baseRepository) {
            $this->baseRepository = $baseRepository;
        } else {
            $objectManager = $objectManager ?? GeneralUtility::makeInstance(ObjectManager::class);
            $this->baseRepository = CoreDocumentRepository::build($objectManager, $objectType);
        }
    }

    /**
     * @param DocumentInterface|object $object
     * @return DocumentInterface
     */
    abstract protected function checkDocumentDatabase(DocumentInterface $object): DocumentInterface;

    public function add($object)
    {
        $this->baseRepository->add($this->checkDocumentDatabase($object));
    }

    public function remove($object)
    {
        $this->baseRepository->remove($this->checkDocumentDatabase($object));
    }

    public function update($modifiedObject)
    {
        $this->baseRepository->update($this->checkDocumentDatabase($modifiedObject));
    }

    /**
     * @param string $uid The identifier of the object to find
     * @return DocumentInterface|null The matching object if found, otherwise NULL
     * @see findByIdentifier()
     */
    public function findByUid($uid)
    {
        return $this->findByIdentifier($uid);
    }

    /**
     * Find an object matching the given GUID
     *
     * In contrast to the default Repositories the method requires the argument to be a GUID string
     *
     * @param string $identifier The identifier of the object to find
     * @return DocumentInterface|null The matching object if found, otherwise NULL
     */
    public function findByIdentifier($identifier)
    {
        if (!is_string($identifier)) {
            throw new InvalidArgumentException(
                'FreeDocumentRepository::findByUid() requires the argument to be a GUID string'
            );
        }

        return $this->findByGuid($identifier);
    }

    public function setDefaultOrderings(array $defaultOrderings)
    {
        throw new BadFunctionCallException(__METHOD__ . ' is not implemented');
    }

    public function setDefaultQuerySettings(QuerySettingsInterface $defaultQuerySettings)
    {
        throw new BadFunctionCallException(__METHOD__ . ' is not implemented');
    }

    public function createQuery()
    {
        throw new BadFunctionCallException(__METHOD__ . ' is not implemented');
    }

    public function findByGuid(string $guid): ?DocumentInterface
    {
        return $this->baseRepository->findByGuid($guid);
    }

    public function findOneByDatabaseAndId(string $database, string $id): ?DocumentInterface
    {
        return $this->baseRepository->findOneByDatabaseAndId($database, $id);
    }

    public function findAllIgnoreDatabase()
    {
        return $this->baseRepository->findAllIgnoreDatabase();
    }
}
