<?php
namespace Pixelant\Importify\Tests\Unit\Domain\Model;

/**
 * Test case.
 *
 * @author Tim Ta <tim.ta@pixelant.se>
 */
class ImportTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \Pixelant\Importify\Domain\Model\Import
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new \Pixelant\Importify\Domain\Model\Import();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getFileReturnsInitialValueForFileReference()
    {
        self::assertEquals(
            null,
            $this->subject->getFile()
        );
    }

    /**
     * @test
     */
    public function setFileForFileReferenceSetsFile()
    {
        $fileReferenceFixture = new \TYPO3\CMS\Extbase\Domain\Model\FileReference();
        $this->subject->setFile($fileReferenceFixture);

        self::assertAttributeEquals(
            $fileReferenceFixture,
            'file',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getFilenameReturnsInitialValueForString()
    {
        self::assertSame(
            '',
            $this->subject->getFilename()
        );
    }

    /**
     * @test
     */
    public function setFilenameForStringSetsFilename()
    {
        $this->subject->setFilename('Conceived at T3CON10');

        self::assertAttributeEquals(
            'Conceived at T3CON10',
            'filename',
            $this->subject
        );
    }
}
