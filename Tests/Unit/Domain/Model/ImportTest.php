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
    public function dummyTestToNotLeaveThisFileEmpty()
    {
        self::markTestIncomplete();
    }
}
