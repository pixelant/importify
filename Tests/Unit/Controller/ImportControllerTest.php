<?php
namespace Pixelant\Importify\Tests\Unit\Controller;

/**
 * Test case.
 *
 * @author Tim Ta <tim.ta@pixelant.se>
 */
class ImportControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \Pixelant\Importify\Controller\ImportController
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder(\Pixelant\Importify\Controller\ImportController::class)
            ->setMethods(['redirect', 'forward', 'addFlashMessage'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function listActionFetchesAllImportsFromRepositoryAndAssignsThemToView()
    {

        $allImports = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $importRepository = $this->getMockBuilder(\Pixelant\Importify\Domain\Repository\ImportRepository::class)
            ->setMethods(['findAll'])
            ->disableOriginalConstructor()
            ->getMock();
        $importRepository->expects(self::once())->method('findAll')->will(self::returnValue($allImports));
        $this->inject($this->subject, 'importRepository', $importRepository);

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $view->expects(self::once())->method('assign')->with('imports', $allImports);
        $this->inject($this->subject, 'view', $view);

        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function showActionAssignsTheGivenImportToView()
    {
        $import = new \Pixelant\Importify\Domain\Model\Import();

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $this->inject($this->subject, 'view', $view);
        $view->expects(self::once())->method('assign')->with('import', $import);

        $this->subject->showAction($import);
    }

    /**
     * @test
     */
    public function createActionAddsTheGivenImportToImportRepository()
    {
        $import = new \Pixelant\Importify\Domain\Model\Import();

        $importRepository = $this->getMockBuilder(\Pixelant\Importify\Domain\Repository\ImportRepository::class)
            ->setMethods(['add'])
            ->disableOriginalConstructor()
            ->getMock();

        $importRepository->expects(self::once())->method('add')->with($import);
        $this->inject($this->subject, 'importRepository', $importRepository);

        $this->subject->createAction($import);
    }

    /**
     * @test
     */
    public function editActionAssignsTheGivenImportToView()
    {
        $import = new \Pixelant\Importify\Domain\Model\Import();

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $this->inject($this->subject, 'view', $view);
        $view->expects(self::once())->method('assign')->with('import', $import);

        $this->subject->editAction($import);
    }

    /**
     * @test
     */
    public function updateActionUpdatesTheGivenImportInImportRepository()
    {
        $import = new \Pixelant\Importify\Domain\Model\Import();

        $importRepository = $this->getMockBuilder(\Pixelant\Importify\Domain\Repository\ImportRepository::class)
            ->setMethods(['update'])
            ->disableOriginalConstructor()
            ->getMock();

        $importRepository->expects(self::once())->method('update')->with($import);
        $this->inject($this->subject, 'importRepository', $importRepository);

        $this->subject->updateAction($import);
    }

    /**
     * @test
     */
    public function deleteActionRemovesTheGivenImportFromImportRepository()
    {
        $import = new \Pixelant\Importify\Domain\Model\Import();

        $importRepository = $this->getMockBuilder(\Pixelant\Importify\Domain\Repository\ImportRepository::class)
            ->setMethods(['remove'])
            ->disableOriginalConstructor()
            ->getMock();

        $importRepository->expects(self::once())->method('remove')->with($import);
        $this->inject($this->subject, 'importRepository', $importRepository);

        $this->subject->deleteAction($import);
    }
}