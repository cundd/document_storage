<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests\Unit\Domain\Model;

use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Exception\InvalidDatabaseNameException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DocumentTest extends TestCase
{
    private Document $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = new Document();
        $this->fixture->setDataProtected(
            (string) json_encode(
                [
                    'firstName' => 'Daniel',
                    'lastName'  => 'Corn',
                    'address'   => [
                        'street'  => 'Bingstreet 1',
                        'city'    => 'Feldkirch',
                        'zip'     => '6800',
                        'country' => 'Austria',
                    ],
                ]
            )
        );
    }

    #[Test]
    public function setContentTest(): void
    {
        $content = '{"data": "The new test content"}';
        $this->fixture->setDataProtected($content);
        $this->assertEquals($content, $this->fixture->getDataProtected());
    }

    #[Test]
    public function getInitialContentTest(): void
    {
        $model = new Document();
        $result = $model->getDataProtected();
        $this->assertNull($result);
    }

    #[Test]
    public function setDbTest(): void
    {
        $db = 'testdb';
        $this->fixture->setDb($db);
        $this->assertEquals($db, $this->fixture->getDb());

        $db = 'testdb1';
        $this->fixture->setDb($db);
        $this->assertEquals($db, $this->fixture->getDb());

        $db = 'test1db2';
        $this->fixture->setDb($db);
        $this->assertEquals($db, $this->fixture->getDb());
    }

    #[Test]
    #[DataProvider('invalidDbDataProvider')]
    public function setInvalidDbTest(string $db): void
    {
        $this->expectException(InvalidDatabaseNameException::class);

        $this->fixture->setDb($db); // @phpstan-ignore argument.type
    }

    /**
     * @return list<array<int,string>>
     */
    public static function invalidDbDataProvider(): array
    {
        return [
            ['test db'],
            ['test^db'],
            ['test%db'],
            [''],
        ];
    }

    #[Test]
    public function getInitialDbTest(): void
    {
        $result = $this->fixture->hasDb();
        $this->assertFalse($result);
    }

    #[Test]
    public function changeGuidTest(): void
    {
        $id = (string) time();
        $database = 'testdb';
        $this->fixture->setId($id);
        $this->fixture->setDb($database);
        $this->assertEquals($database . '/' . $id, $this->fixture->getGuid());
    }

    #[Test]
    public function getInitialGuidTest(): void
    {
        $result = $this->fixture->getGuid();
        $this->assertNull($result);
    }

    #[Test]
    public function valueForKeyTest(): void
    {
        $key = 'firstName';
        $result = $this->fixture->valueForKey($key);
        $this->assertEquals('Daniel', $result);
    }

    #[Test]
    public function valueForKeyPathTest(): void
    {
        $keyPath = 'address.street';
        $result = $this->fixture->valueForKeyPath($keyPath);
        $this->assertEquals('Bingstreet 1', $result);
    }

    #[Test]
    public function valueForKeyPathUndefinedTest(): void
    {
        $keyPath = 'address.street.direction';
        $result = $this->fixture->valueForKeyPath($keyPath);
        $this->assertNull($result);
    }

    #[Test]
    public function valueForKeyPathUndefinedWithDefaultTest(): void
    {
        $default = 'south';
        $keyPath = 'address.street.direction';
        $result = $this->fixture->valueForKeyPath($keyPath, $default);
        $this->assertSame($default, $result);
    }

    #[Test]
    public function valueForUndefinedKeyTest(): void
    {
        $undefinedKey = 'undefined';
        $result = $this->fixture->valueForUndefinedKey($undefinedKey);
        $this->assertNull($result);
    }

    #[Test]
    public function setValueForKeyTest(): void
    {
        $function = 'Superman';
        $key = 'function';

        $this->fixture->setValueForKey($key, $function);
        $this->assertEquals($function, $this->fixture->valueForKey($key));
    }
}
