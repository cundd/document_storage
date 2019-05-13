<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests\Unit\Domain\Repository;

use Cundd\DocumentStorage\Domain\Repository\DocumentRepository;
use Cundd\DocumentStorage\Domain\Repository\DocumentRepositoryInterface;
use Cundd\DocumentStorage\Persistence\Repository\CoreDocumentRepositoryInterface;
use function bin2hex;
use function random_bytes;

class DocumentRepositoryTest extends AbstractDocumentRepositoryCase
{
    /**
     * @var string
     */
    private $testDatabaseName;

    protected function setUp()
    {
        parent::setUp();
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->testDatabaseName = bin2hex(random_bytes(12));
    }

    protected function tearDown()
    {
        unset($this->testDatabaseName);
        parent::tearDown();
    }

    protected function buildRepositoryWithCore(
        CoreDocumentRepositoryInterface $coreDocumentRepository
    ): DocumentRepositoryInterface {
        return new DocumentRepository(null, $this->getTestDatabaseName(), $coreDocumentRepository);
    }

    protected function getTestDatabaseName(): string
    {
        return $this->testDatabaseName;
    }
}
