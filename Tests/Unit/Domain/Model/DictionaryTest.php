<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests\Unit\Domain\Model;

use Cundd\DocumentStorage\Domain\Model\Dictionary;
use Cundd\DocumentStorage\Domain\Model\Document;

class DictionaryTest extends AbstractDocumentCase
{
    /**
     * @var Dictionary
     */
    protected $fixture = null;

    protected function buildDocument(): Document
    {
        return new Dictionary();
    }

    /**
     * @test
     */
    public function offsetExistsTest()
    {
        $this->assertTrue($this->fixture->offsetExists('firstName'));
    }

    /**
     * @test
     */
    public function offsetExistsArrayTest()
    {
        $this->assertTrue(isset($this->fixture['firstName']));
    }

    /**
     * @test
     */
    public function offsetGetTest()
    {
        $this->assertEquals('Daniel', $this->fixture->offsetGet('firstName'));
    }

    /**
     * @test
     */
    public function offsetGetArrayTest()
    {
        $this->assertEquals('Daniel', $this->fixture['firstName']);
    }

    /**
     * @test
     */
    public function offsetSetTest()
    {
        $function = 'Superman';
        $key = 'function';

        $this->fixture->offsetSet($key, $function);
        $this->assertEquals($function, $this->fixture->valueForKey($key));
    }

    /**
     * @test
     */
    public function offsetSetArrayTest()
    {
        $function = 'Superman';
        $key = 'function';

        $this->fixture[$key] = $function;
        $this->assertEquals($function, $this->fixture->valueForKey($key));
    }

    /**
     * @test
     */
    public function offsetUnsetTest()
    {
        $key = 'firstName';
        $this->fixture->offsetUnset($key);
        $this->assertNull($this->fixture->valueForKey($key));
    }

    /**
     * @test
     */
    public function offsetUnsetArrayTest()
    {
        $key = 'firstName';
        unset($this->fixture[$key]);
        $this->assertNull($this->fixture->valueForKey($key));
    }
}
