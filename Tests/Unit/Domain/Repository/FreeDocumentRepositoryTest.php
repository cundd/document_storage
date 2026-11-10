<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests\Unit\Domain\Repository;

use Cundd\DocumentStorage\Domain\Model\DocumentInterface;
use Cundd\DocumentStorage\Domain\Repository\DocumentRepositoryInterface;
use Cundd\DocumentStorage\Domain\Repository\FreeDocumentRepository;
use Cundd\DocumentStorage\Persistence\Repository\CoreDocumentRepositoryInterface;
use Prophecy\Prophecy\ObjectProphecy;

use function count;

final class FreeDocumentRepositoryTest extends AbstractDocumentRepositoryCase
{
    protected function buildRepositoryWithCore(
        CoreDocumentRepositoryInterface $coreDocumentRepository,
    ): DocumentRepositoryInterface {
        return new FreeDocumentRepository($coreDocumentRepository); // @phpstan-ignore argument.type, return.type
    }

    protected function getTestDatabaseName(): string
    {
        return 'some-database';
    }

    public function testAdd(): void
    {
        $document = $this->buildDocument([
            'name' => 'Daniel',
            'job'  => 'Webdeveloper',
            'db'   => $this->getTestDatabaseName(),
        ]);
        $this->buildRepository(
            function ($configureBaseRepositoryProphecy) use ($document) {
                $configureBaseRepositoryProphecy
                    ->add($document)
                    ->shouldBeCalled();
            }
        )->add($document);
    }

    public function testUpdate(): void
    {
        $document = $this->buildDocument([
            'name' => 'Daniel',
            'job'  => 'Webdeveloper',
            'db'   => $this->getTestDatabaseName(),
        ]);
        $this->buildRepository(
            function ($configureBaseRepositoryProphecy) use ($document) {
                $configureBaseRepositoryProphecy
                    ->update($document)
                    ->shouldBeCalled();
            }
        )->update($document);
    }

    public function testFindByDatabase(): void
    {
        $database = $this->getTestDatabaseName();
        $documents = [
            $this->buildDocument(['name' => 'Daniel', 'job' => 'Webdeveloper']),
            $this->buildDocument(['name' => 'Peter', 'job' => 'Banker']),
        ];
        $result = $this->buildRepository(
            function (ObjectProphecy $prophecy) use ($database, $documents) {
                $prophecy
                    ->findByDatabase($database)
                    ->willReturn($documents)
                    ->shouldBeCalled();
            }
        )->findByDatabase($database);
        $this->assertSame(count($documents), count($result));
    }

    public function testRemoveAllFromDatabase(): void
    {
        $database = $this->getTestDatabaseName();
        $this->buildRepository(
            function (ObjectProphecy $prophecy) use ($database) {
                $prophecy->removeAllFromDatabase($database)->shouldBeCalled();
            }
        )->removeAllFromDatabase($database);
    }

    public function testCountByDatabase(): void
    {
        $expectedCount = 192;
        $database = $this->getTestDatabaseName();
        $result = $this->buildRepository(
            function (ObjectProphecy $prophecy) use ($expectedCount, $database) {
                $prophecy
                    ->countByDatabase($database)
                    ->willReturn($expectedCount)
                    ->shouldBeCalled();
            }
        )->countByDatabase($database);
        $this->assertSame($expectedCount, $result);
    }

    /**
     * @return FreeDocumentRepository<DocumentInterface>
     */
    protected function buildRepository(
        ?callable $configureCoreRepositoryProphecy = null,
    ): DocumentRepositoryInterface {
        return parent::buildRepository($configureCoreRepositoryProphecy); // @phpstan-ignore return.type
    }
}
