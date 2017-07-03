<?php
namespace Pixelant\Importify\Tests\Unit\Domain\Model;

/**
 * Test case.
 *
 * @author Tim Ta <tim.ta@pixelant.se>
 */
class FileReference extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \Pixelant\Importify\Domain\Model\FileReference
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new \Pixelant\Importify\Domain\Model\FileReference();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function setOriginalResourceTest()
    {
        $resource = $this->getMock(
            \TYPO3\CMS\Core\Resource\FileReference::class,
            ['getOriginalFile','getUid'],
            [],
            '',
            false
        );
        $resource->expects($this->once())->method('getOriginalFile')->will($this->returnValue($resource));
        $resource->expects($this->once())->method('getUid')->will($this->returnValue($resource));
        $this->subject->setOriginalResource($resource);
    }
}
