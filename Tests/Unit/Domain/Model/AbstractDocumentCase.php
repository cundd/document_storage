<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests\Unit\Domain\Model;

use Cundd\DocumentStorage\Domain\Model\Document;
use PHPUnit\Framework\TestCase;

/**
 * Document test case
 */
abstract class AbstractDocumentCase extends TestCase
{
    /**
     * @var Document
     */
    protected $fixture = null;

    /**
     * @return Document
     */
    abstract protected function buildDocument(): Document;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->buildDocument();
        $this->fixture->setDataProtected(
            json_encode(
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

    public function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function setContentTest()
    {
        $content = '{"data": "The new test content"}';
        $this->fixture->setDataProtected($content);
        $this->assertEquals($content, $this->fixture->getDataProtected());
    }

    /**
     * @test
     */
    public function getInitialContentTest()
    {
        $model = $this->buildDocument();
        $result = $model->getDataProtected();
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function setDbTest()
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

    /**
     * @expectException \Cundd\Rest\Domain\Exception\InvalidDatabaseNameException
     */
    public function setInvalidDbTest()
    {
        $db = 'test-db';
        $this->fixture->setDb($db);
    }

    /**
     * @test
     */
    public function getInitialDbTest()
    {
        $result = $this->fixture->getDb();
        $this->assertEquals('', $result);
    }

    /**
     * @test
     */
    public function changeGuidTest()
    {
        $id = (string)time();
        $database = 'testdb';
        $this->fixture->setId($id);
        $this->fixture->setDb($database);
        $this->assertEquals($database . '/' . $id, $this->fixture->getGuid());
    }

    /**
     * @test
     */
    public function getInitialGuidTest()
    {
        $result = $this->fixture->getGuid();
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function valueForKeyTest()
    {
        $key = 'firstName';
        $result = $this->fixture->valueForKey($key);
        $this->assertEquals('Daniel', $result);
    }

    /**
     * @test
     */
    public function valueForKeyPathTest()
    {
        $keyPath = 'address.street';
        $result = $this->fixture->valueForKeyPath($keyPath);
        $this->assertEquals('Bingstreet 1', $result);
    }

    /**
     * @test
     */
    public function valueForKeyPathUndefinedTest()
    {
        $keyPath = 'address.street.direction';
        $result = $this->fixture->valueForKeyPath($keyPath);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function valueForKeyPathUndefinedWithDefaultTest()
    {
        $default = 'south';
        $keyPath = 'address.street.direction';
        $result = $this->fixture->valueForKeyPath($keyPath, $default);
        $this->assertSame($default, $result);
    }

    /**
     * @test
     */
    public function valueForUndefinedKeyTest()
    {
        $undefinedKey = 'undefined';
        $result = $this->fixture->valueForUndefinedKey($undefinedKey);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function setValueForKeyTest()
    {
        $function = 'Superman';
        $key = 'function';

        $this->fixture->setValueForKey($key, $function);
        $this->assertEquals($function, $this->fixture->valueForKey($key));
    }

    //	/**
    //	 * @test
    //	 */
    //	public function setValueForKeyPathTest() {
    //		$value = 'Antarctic';
    //		$keyPath = 'address.country';
    //		$this->fixture->setValueForKeyPath($value, $keyPath);
    //		$this->assertEquals($value, $this->fixture->valueForKeyPath($keyPath));
    //	}
}
