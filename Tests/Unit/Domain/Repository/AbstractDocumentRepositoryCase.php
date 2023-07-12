<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests\Unit\Domain\Repository;

use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Domain\Repository\DocumentRepositoryInterface;
use Cundd\DocumentStorage\Domain\Repository\FreeDocumentRepository;
use Cundd\DocumentStorage\Persistence\Repository\CoreDocumentRepositoryInterface;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use function count;

use const PHP_INT_MAX;

abstract class AbstractDocumentRepositoryCase extends TestCase
{
    use ProphecyTrait;

    abstract protected function buildRepositoryWithCore(
        CoreDocumentRepositoryInterface $coreDocumentRepository
    ): DocumentRepositoryInterface;

    abstract protected function getTestDatabaseName(): string;

    public function testFindWithProperties()
    {
        $database = $this->getTestDatabaseName();
        $documents = [
            $this->buildDocument(['name' => 'Daniel', 'job' => 'Webdeveloper']),
            $this->buildDocument(['name' => 'Peter', 'job' => 'Banker']),
        ];

        $properties = [
            'db'   => $database,
            'name' => 'not really tested in this unit test',
        ];

        $result = $this->buildRepository(
            function (ObjectProphecy $prophecy) use ($properties, $documents) {
                /** @var CoreDocumentRepositoryInterface $prophecy */
                $prophecy->findWithProperties($properties, PHP_INT_MAX)->willReturn(
                    $documents
                )->shouldBeCalled();
            }
        )->findWithProperties($properties);
        $this->assertSame(count($documents), count($result));
    }

    public function testRemoveAll()
    {
        $database = $this->getTestDatabaseName();
        $documentRepository = $this->buildRepository(
            function (ObjectProphecy $prophecy) use ($database) {
                /** @var CoreDocumentRepositoryInterface $prophecy */
                /** @noinspection PhpVoidFunctionResultUsedInspection */
                $prophecy->removeAllFromDatabase($database)->shouldBeCalled();
            }
        );
        if ($documentRepository instanceof FreeDocumentRepository) {
            $documentRepository->removeAll($database);
        } else {
            $documentRepository->removeAll();
        }
    }

    public function testFindAll()
    {
        $database = $this->getTestDatabaseName();
        $documents = [
            $this->buildDocument(['name' => 'Daniel', 'job' => 'Webdeveloper']),
            $this->buildDocument(['name' => 'Peter', 'job' => 'Banker']),
        ];
        $documentRepository = $this->buildRepository(
            function (ObjectProphecy $prophecy) use ($database, $documents) {
                /** @var CoreDocumentRepositoryInterface $prophecy */
                $prophecy->findByDatabase($database)->willReturn($documents)->shouldBeCalled();
            }
        );
        if ($documentRepository instanceof FreeDocumentRepository) {
            $result = $documentRepository->findAll($database);
        } else {
            $result = $documentRepository->findAll();
        }
        $this->assertSame(count($documents), count($result));
    }

    public function testCountAll()
    {
        $expectedCount = 192;
        $database = $this->getTestDatabaseName();
        $documentRepository = $this->buildRepository(
            function (ObjectProphecy $prophecy) use ($expectedCount, $database) {
                /** @var CoreDocumentRepositoryInterface $prophecy */
                $prophecy->countByDatabase($database)->willReturn(
                    $expectedCount
                )->shouldBeCalled();
            }
        );
        if ($documentRepository instanceof FreeDocumentRepository) {
            $result = $documentRepository->countAll($database);
        } else {
            $result = $documentRepository->countAll();
        }
        $this->assertSame($expectedCount, $result);
    }

    /**
     * @param callable|null $configureCoreRepositoryProphecy
     * @return DocumentRepositoryInterface
     */
    protected function buildRepository(?callable $configureCoreRepositoryProphecy = null): DocumentRepositoryInterface
    {
        $coreRepositoryProphecy = $this->prophesize(CoreDocumentRepositoryInterface::class);
        if ($configureCoreRepositoryProphecy) {
            $configureCoreRepositoryProphecy($coreRepositoryProphecy);
        }

        /** @var CoreDocumentRepositoryInterface $coreRepository */
        $coreRepository = $coreRepositoryProphecy->reveal();

        return $this->buildRepositoryWithCore($coreRepository);
    }

    public static function buildDocument(array $data = null): DocumentInterface
    {
        if (null === $data) {
            $data = ['name' => 'Daniel', 'job' => 'Webdeveloper', 'dayOfBirth' => new DateTime()];
        }
        $document = new Document();
        foreach ($data as $key => $value) {
            $document->setValueForKey($key, $value);
        }

        return $document;
    }
}
