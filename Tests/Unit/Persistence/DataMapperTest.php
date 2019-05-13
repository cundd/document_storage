<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests\Unit\Persistence;

use Cundd\DocumentStorage\Domain\Model\Document;
use Cundd\DocumentStorage\Persistence\DataMapper;
use DateTime;
use PHPUnit\Framework\TestCase;

class DataMapperTest extends TestCase
{
    /**
     * @var DataMapper
     */
    private $fixture;

    protected function setUp()
    {
        parent::setUp();
        $this->fixture = new DataMapper();
    }

    protected function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    public function testMapSingleRow()
    {
        $dateTime = new DateTime('now');
        $result = $this->fixture->mapSingleRow(
            Document::class,
            ['name' => 'Daniel', 'job' => 'Webdeveloper', 'dayOfBirth' => $dateTime]
        );

        $this->assertInstanceOf(Document::class, $result);
        $this->assertSame('Daniel', $result->valueForKey('name'));
        $this->assertSame('Webdeveloper', $result->valueForKey('job'));
        $this->assertSame($dateTime, $result->valueForKey('dayOfBirth'));
    }

    public function testMap()
    {
        $rows = [
            ['name' => 'Daniel', 'job' => 'Webdeveloper', 'dayOfBirth' => new DateTime('now')],
            ['name' => 'Peter', 'job' => 'Banker', 'dayOfBirth' => new DateTime('yesterday')],
        ];
        $results = $this->fixture->map(Document::class, $rows);

        $this->assertIsArray($results);
        foreach ($results as $i => $result) {
            $this->assertInstanceOf(Document::class, $result);
            $this->assertSame($rows[$i]['name'], $result->valueForKey('name'));
            $this->assertSame($rows[$i]['job'], $result->valueForKey('job'));
            $this->assertSame($rows[$i]['dayOfBirth'], $result->valueForKey('dayOfBirth'));
        }
    }
}
