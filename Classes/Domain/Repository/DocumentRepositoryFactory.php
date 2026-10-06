<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Repository;

use Cundd\DocumentStorage\DocumentFilter;
use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Persistence\Repository\CoreDocumentRepository;
use Cundd\DocumentStorage\Persistence\Repository\CoreDocumentRepositoryInterface;
use InvalidArgumentException;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

use function is_subclass_of;
use function sprintf;

final class DocumentRepositoryFactory
{
    public function __construct(
        private readonly DocumentFilter $documentFilter,
        private readonly PersistenceManagerInterface $persistenceManager,
    ) {
    }

    /**
     * @template T of DocumentInterface
     *
     * @param class-string<T> $objectType
     *
     * @return CoreDocumentRepositoryInterface<T>
     *
     * @internal
     */
    public function buildCoreDocumentRepository(
        string $objectType = Document::class,
    ): CoreDocumentRepositoryInterface {
        return CoreDocumentRepository::build(
            $objectType,
            $this->documentFilter,
            $this->persistenceManager,
        );
    }

    /**
     * @template T of DocumentInterface
     * @template R of DocumentRepository<T>
     *
     * @param non-empty-string $database
     * @param class-string<T>  $objectType
     * @param class-string<R>  $repositoryClass
     *
     * @return ($repositoryClass is string ? R : DocumentRepository<T>)
     */
    public function buildDocumentRepository(
        string $database,
        string $objectType = Document::class,
        ?string $repositoryClass = null,
    ): DocumentRepository {
        $baseDocumentRepository = CoreDocumentRepository::build(
            $objectType,
            $this->documentFilter,
            $this->persistenceManager,
        );
        if (null === $repositoryClass) {
            return new DocumentRepository(
                $database,
                $baseDocumentRepository,
                $objectType
            );
        }

        if (is_subclass_of($repositoryClass, DocumentRepository::class)) {
            return new $repositoryClass(
                $database,
                $baseDocumentRepository,
                $objectType
            );
        }

        throw new InvalidArgumentException(
            sprintf(
                'Given repository class "%s" isn\'t a subclass of %s',
                $repositoryClass,
                DocumentRepository::class
            )
        );
    }

    /**
     * @template T of DocumentInterface
     * @template R of FreeDocumentRepository<T>
     *
     * @param class-string<T> $objectType
     * @param class-string<R> $repositoryClass
     *
     * @return ($repositoryClass is string ? R : FreeDocumentRepository<T>)
     */
    public function buildFreeDocumentRepository(
        string $objectType = Document::class,
        ?string $repositoryClass = null,
    ): FreeDocumentRepository {
        $baseDocumentRepository = CoreDocumentRepository::build(
            $objectType,
            $this->documentFilter,
            $this->persistenceManager,
        );
        if (null === $repositoryClass) {
            return new FreeDocumentRepository($baseDocumentRepository, $objectType);
        }

        if (is_subclass_of($repositoryClass, FreeDocumentRepository::class)) {
            return new $repositoryClass($baseDocumentRepository, $objectType);
        }

        throw new InvalidArgumentException(
            sprintf(
                'Given repository class "%s" isn\'t a subclass of %s',
                $repositoryClass,
                FreeDocumentRepository::class
            )
        );
    }
}
