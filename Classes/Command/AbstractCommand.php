<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Command;

use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Domain\Repository\DatabaseRepository;
use Cundd\DocumentStorage\Domain\Repository\DocumentRepositoryFactory;
use Cundd\DocumentStorage\Domain\Repository\FreeDocumentRepository;
use Cundd\DocumentStorage\Persistence\DataMapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

abstract class AbstractCommand extends Command
{
    use DocumentOutputTrait;

    /**
     * @var FreeDocumentRepository<Document>
     */
    private FreeDocumentRepository $documentRepository;

    public function __construct(
        private readonly DocumentRepositoryFactory $documentRepositoryFactory,
        protected readonly DataMapper $dataMapper,
        private readonly PersistenceManagerInterface $persistenceManager,
        protected readonly DatabaseRepository $databaseRepository,
        $name = null,
    ) {
        parent::__construct($name);
    }

    /**
     * @param non-empty-string $db
     * @param non-empty-string $id
     */
    protected function getDocument(
        OutputInterface $output,
        string $db,
        string $id,
    ): ?Document {
        /** @var Document $document */
        $document = $this->getDocumentRepository()->findOneByDatabaseAndId($db, $id);
        if ($document) {
            return $document;
        } else {
            $output->writeln("<error>Document $db/$id not found</error>");

            return null;
        }
    }

    /**
     * @return FreeDocumentRepository<Document>
     */
    protected function getDocumentRepository(): FreeDocumentRepository
    {
        if (!isset($this->documentRepository)) {
            $this->documentRepository = $this->documentRepositoryFactory->buildFreeDocumentRepository();
        }

        return $this->documentRepository;
    }

    protected function persistChanges(): void
    {
        $this->persistenceManager->persistAll();
    }
}
