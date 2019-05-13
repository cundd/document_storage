<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Repository;

use Cundd\DocumentStorage\Persistence\Repository\CoreDocumentRepositoryInterface;
use Cundd\DocumentStorage\Persistence\Repository\FixedDatabaseBridge;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use function str_replace;
use function strtolower;

/**
 * An abstract Document Repository that can be extended to interface the Documents with a custom Repository
 *
 * Only Documents of the specified Database can be managed by this Repository. So this is a more traditional Repository.
 * To manage Documents belonging to any Database use the `FreeDocumentRepository`.
 * For a Repository that does not need to be subclassed use the `DocumentRepository`.
 */
abstract class AbstractDocumentRepository extends FixedDatabaseBridge
{
    /**
     * Abstract Document Repository constructor
     *
     * @param ObjectManagerInterface|null          $objectManager
     * @param CoreDocumentRepositoryInterface|null $baseRepository
     */
    public function __construct(
        ?ObjectManagerInterface $objectManager = null,
        ?CoreDocumentRepositoryInterface $baseRepository = null
    ) {
        $objectType = ClassNamingUtility::translateRepositoryNameToModelName($this->getRepositoryClassName());
        $database = strtolower(str_replace('\\', '_', $objectType));
        parent::__construct($objectManager, $database, $baseRepository, $objectType);
    }

    /**
     * Return the name of the database managed by this repository
     *
     * Overwrite this to define a custom database name to be used by this repository
     *
     * @return string
     */
    protected function getDatabase(): string
    {
        return parent::getDatabase();
    }


    protected function getRepositoryClassName(): string
    {
        return static::class;
    }
}
