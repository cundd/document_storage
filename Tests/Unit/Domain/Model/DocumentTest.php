<?php
namespace Cundd\DocumentStorage\Tests\Unit\Domain\Model;

/**
 * Test case.
 *
 * @author Daniel Corn <info@cundd.net>
 */
class DocumentTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var \Cundd\DocumentStorage\Domain\Model\Document
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new \Cundd\DocumentStorage\Domain\Model\Document();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getIdReturnsInitialValueForString()
    {
        self::assertSame(
            '',
            $this->subject->getId()
        );
    }

    /**
     * @test
     */
    public function setIdForStringSetsId()
    {
        $this->subject->setId('Conceived at T3CON10');

        self::assertAttributeEquals(
            'Conceived at T3CON10',
            'id',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getDbReturnsInitialValueForString()
    {
        self::assertSame(
            '',
            $this->subject->getDb()
        );
    }

    /**
     * @test
     */
    public function setDbForStringSetsDb()
    {
        $this->subject->setDb('Conceived at T3CON10');

        self::assertAttributeEquals(
            'Conceived at T3CON10',
            'db',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getDataProtectedReturnsInitialValueForString()
    {
        self::assertSame(
            '',
            $this->subject->getDataProtected()
        );
    }

    /**
     * @test
     */
    public function setDataProtectedForStringSetsDataProtected()
    {
        $this->subject->setDataProtected('Conceived at T3CON10');

        self::assertAttributeEquals(
            'Conceived at T3CON10',
            'dataProtected',
            $this->subject
        );
    }
}
