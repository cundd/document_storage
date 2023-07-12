<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests\Unit\Domain\Repository;

use Cundd\DocumentStorage\Persistence\Repository\CoreDocumentRepositoryInterface;
use Prophecy\Prophecy\ObjectProphecy;

abstract class FixedDatabaseBridgeCase extends AbstractDocumentRepositoryCase
{
    public function testAdd()
    {
        $document = $this->buildDocument(['name' => 'Daniel', 'job' => 'Webdeveloper']);
        $this->buildRepository(
            function (ObjectProphecy $prophecy) use ($document) {
                /** @var CoreDocumentRepositoryInterface $prophecy */
                $prophecy->add($document)->shouldBeCalled();
            }
        )->add($document);
    }

    /**
     * @expectedException \Cundd\DocumentStorage\Exception\InvalidDocumentDatabaseException
     */
    public function testAddWithDatabaseMismatch()
    {
        $document = $this->buildDocument(['name' => 'Daniel', 'job' => 'Webdeveloper', 'db' => 'something-else']);
        $this->buildRepository()->add($document);
    }

    public function testUpdate()
    {
        $document = $this->buildDocument(
            ['name' => 'Daniel', 'job' => 'Webdeveloper']
        );
        $this->buildRepository(
            function (ObjectProphecy $prophecy) use ($document) {
                /** @var CoreDocumentRepositoryInterface $prophecy */
                $prophecy->update($document)->shouldBeCalled();
            }
        )->update($document);
    }

    /**
     * @expectedException \Cundd\DocumentStorage\Exception\InvalidDocumentDatabaseException
     */
    public function testUpdateWithDatabaseMismatch()
    {
        $document = $this->buildDocument(
            ['name' => 'Daniel', 'job' => 'Webdeveloper', 'db' => 'something-else']
        );
        $this->buildRepository()->update($document);
    }

    public function testRemove()
    {
        $document = $this->buildDocument(
            ['name' => 'Daniel', 'job' => 'Webdeveloper']
        );
        $this->buildRepository(
            function (ObjectProphecy $prophecy) use ($document) {
                /** @var CoreDocumentRepositoryInterface $prophecy */
                $prophecy->remove($document)->shouldBeCalled();
            }
        )->remove($document);
    }

    /**
     * @expectedException \Cundd\DocumentStorage\Exception\InvalidDocumentDatabaseException
     */
    public function testRemoveWithDatabaseMismatch()
    {
        $document = $this->buildDocument(
            ['name' => 'Daniel', 'job' => 'Webdeveloper', 'db' => 'something-else']
        );
        $this->buildRepository()->remove($document);
    }
}
