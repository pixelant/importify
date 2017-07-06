<?php
namespace Pixelant\Importify\Tests\Unit\ViewHelpers\Form;

/**
 * Test case.
 *
 * @author Tim Ta <tim.ta@pixelant.se>
 */
class UploadViewHelperTest extends \TYPO3\CMS\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase
{
    /**
     * @var \Pixelant\Importify\ViewHelpers\Form\UploadViewHelper
     */
    protected $subject = null;
    /**
     * @var \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper
     */
    protected $viewHelper;
    /**
     * @var \TYPO3\CMS\Fluid\Core\ViewHelper\Arguments
     */
    protected $mockArguments;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMock(
            \Pixelant\Importify\ViewHelpers\Form\UploadViewHelper::class,
            ['redirect', 'forward', 'addFlashMessage'],
            [],
            '',
            false
        );

        $this->viewHelper = $this->getAccessibleMock(
            \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper::class,
            array('getRenderingContext', 'renderChildren', 'hasArgument')
        );
        $this->viewHelper->expects($this->any())->method('getRenderingContext')
        ->will($this->returnValue($this->renderingContext));
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function renderTest()
    {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $fileReference = $this->getMock(
            \TYPO3\CMS\Extbase\Domain\Model\FileReference::class,
            ['getUid','getOriginalResource'],
            [],
            '',
            false
        );
        $propertyMapper = $this->getMock(
            \TYPO3\CMS\Extbase\Property\PropertyMapper::class,
            ['convert'],
            [],
            '',
            false
        );

        $this->inject($this->subject, 'propertyMapper', $propertyMapper);
        $propertyMapper->expects(self::any())->method(
            'convert'
        )->will($this->returnValue($fileReference));

        $file = $this->getMock(\TYPO3\CMS\Core\Resource\File::class, ['getUid'], [], '', false);
        $file->expects($this->any())->method('getUid')->will($this->returnValue(1));

        $originalResource = $this->getMock(\TYPO3\CMS\Core\Resource\File::class, ['getOriginalFile'], [], '', false);
        $originalResource->expects($this->any())->method('getOriginalFile')->will($this->returnValue($file));

        $fileReference->expects(
            $this->any()
        )->method('getOriginalResource')->will($this->returnValue($originalResource));

        $hashService = $this->getMock(
            \TYPO3\CMS\Extbase\Security\Cryptography\HashService::class,
            ['appendHmac'],
            [],
            '',
            false
        );
        $this->inject($this->subject, 'hashService', $hashService);
        $hashService->expects(self::any())->method('appendHmac');

        $persistenceManager = $this->getMock(
            \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class,
            ['add','remove'],
            [],
            '',
            false
        );
        $this->inject($this->subject, 'templateVariableContainer', $persistenceManager);

        $mockThenViewHelperNode = $this->getMock(
            \TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\ViewHelperNode::class,
            array('getViewHelperClassName', 'evaluate'),
            array(),
            '',
            false
        );
        $mockThenViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')
        ->will($this->returnValue(\TYPO3\CMS\Fluid\ViewHelpers\ThenViewHelper::class));
        $mockThenViewHelperNode->expects($this->at(1))->method('evaluate')
        ->with($this->renderingContext)
        ->will($this->returnValue('ThenViewHelperResults'));

        $this->viewHelper->setChildNodes(array($mockThenViewHelperNode));
        $actualResult = $this->viewHelper->_call('renderThenChild');
        $this->assertEquals('ThenViewHelperResults', $actualResult);

        $this->subject->render();
    }
}
