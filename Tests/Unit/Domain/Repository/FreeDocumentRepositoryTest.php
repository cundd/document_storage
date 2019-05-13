<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests\Unit\Domain\Repository;

use Cundd\DocumentStorage\Domain\Repository\DocumentRepositoryInterface;
use Cundd\DocumentStorage\Domain\Repository\FreeDocumentRepository;
use Cundd\DocumentStorage\Persistence\Repository\CoreDocumentRepositoryInterface;
use Prophecy\Prophecy\ObjectProphecy;
use function count;

class FreeDocumentRepositoryTest extends AbstractDocumentRepositoryCase
{
    protected function buildRepositoryWithCore(
        CoreDocumentRepositoryInterface $coreDocumentRepository
    ): DocumentRepositoryInterface {
        return new FreeDocumentRepository(null, $coreDocumentRepository);
    }

    protected function getTestDatabaseName(): string
    {
        return 'some-datbase';
    }

    public function testAdd()
    {
        $document = $this->buildDocument(
            ['name' => 'Daniel', 'job' => 'Webdeveloper', 'db' => $this->getTestDatabaseName()]
        );
        $this->buildRepository(
            function ($configureBaseRepositoryProphecy) use ($document) {
                /** @var ObjectProphecy|CoreDocumentRepositoryInterface $configureBaseRepositoryProphecy */
                $configureBaseRepositoryProphecy->add($document)->shouldBeCalled();
            }
        )->add($document);
    }

    public function testUpdate()
    {
        $document = $this->buildDocument(
            ['name' => 'Daniel', 'job' => 'Webdeveloper', 'db' => $this->getTestDatabaseName()]
        );
        $this->buildRepository(
            function ($configureBaseRepositoryProphecy) use ($document) {
                /** @var ObjectProphecy|CoreDocumentRepositoryInterface $configureBaseRepositoryProphecy */
                $configureBaseRepositoryProphecy->update($document)->shouldBeCalled();
            }
        )->update($document);
    }

    public function testFindByDatabase()
    {
        $database = $this->getTestDatabaseName();
        $documents = [
            $this->buildDocument(['name' => 'Daniel', 'job' => 'Webdeveloper']),
            $this->buildDocument(['name' => 'Peter', 'job' => 'Banker']),
        ];
        $result = $this->buildRepository(
            function (ObjectProphecy $prophecy) use ($database, $documents) {
                /** @var CoreDocumentRepositoryInterface $prophecy */
                $prophecy->findByDatabase($database)->willReturn($documents)->shouldBeCalled();
            }
        )->findByDatabase($database);
        $this->assertSame(count($documents), count($result));
    }

    public function testRemoveAllFromDatabase()
    {
        $database = $this->getTestDatabaseName();
        $this->buildRepository(
            function (ObjectProphecy $prophecy) use ($database) {
                /** @var CoreDocumentRepositoryInterface $prophecy */
                /** @noinspection PhpVoidFunctionResultUsedInspection */
                $prophecy->removeAllFromDatabase($database)->shouldBeCalled();
            }
        )->removeAllFromDatabase($database);
    }

    public function testCountByDatabase()
    {
        $expectedCount = 192;
        $database = $this->getTestDatabaseName();
        $result = $this->buildRepository(
            function (ObjectProphecy $prophecy) use ($expectedCount, $database) {
                /** @var CoreDocumentRepositoryInterface $prophecy */
                $prophecy->countByDatabase($database)->willReturn($expectedCount)->shouldBeCalled();
            }
        )->countByDatabase($database);
        $this->assertSame($expectedCount, $result);
    }

    /**
     * @param callable $configureCoreRepositoryProphecy
     * @return FreeDocumentRepository
     */
    protected function buildRepository(?callable $configureCoreRepositoryProphecy = null): DocumentRepositoryInterface
    {
        return parent::buildRepository($configureCoreRepositoryProphecy);
    }
}
