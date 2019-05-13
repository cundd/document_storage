<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests\Unit\Domain\Repository;

use Cundd\DocumentStorage\Domain\Repository\DocumentRepositoryInterface;
use Cundd\DocumentStorage\Persistence\Repository\CoreDocumentRepositoryInterface;
use Cundd\DocumentStorage\Persistence\Repository\FixedDatabaseBridge;
use Cundd\DocumentStorage\Tests\Fixture\DummyDocumentRepository;

class AbstractDocumentRepositoryTest extends FixedDatabaseBridge
{
    protected function buildRepositoryWithCore(
        CoreDocumentRepositoryInterface $coreDocumentRepository
    ): DocumentRepositoryInterface {
        return new DummyDocumentRepository(null, $coreDocumentRepository);
    }

    protected function getTestDatabaseName(): string
    {
        // This is the database name automatically generated for `\Cundd\DocumentStorage\Tests\Fixture\DummyDocumentRepository`
        return 'cundd_documentstorage_tests_fixture_dummydocument';
    }
}
