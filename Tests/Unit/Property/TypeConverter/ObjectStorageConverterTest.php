<?php
namespace Pixelant\Importify\Tests\Unit\Property\TypeConverter;

/**
 * Test case.
 *
 * @author Tim Ta <tim.ta@pixelant.se>
 */
class ObjectStorageConverterTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \Pixelant\Importify\Property\TypeConverter\ObjectStorageConverter
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMock(
            \Pixelant\Importify\Property\TypeConverter\ObjectStorageConverter::class,
            ['getPropertyMappingConfiguration'],
            [],
            '',
            false
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getSourceChildPropertiesToBeConvertedTest()
    {
        $propertyValue['tmp_name'] = 'tmp_name';
        $propertyValue['error'] = false;
        $propertyValue['submittedFile']['resourcePointer'] = 'resourcePointer';
        $source['1'] = $propertyValue;
        $this->assertFalse(empty($this->subject->getSourceChildPropertiesToBeConverted($source)));
    }
}
