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
        $import = $this->getMock(
            \Pixelant\Importify\Domain\Model\Import::class,
            ['getFile', 'getDelimeter', 'getEnclosure'],
            [],
            '',
            false
        );

        // CSV
        $csvContent = 'name,age,password
"name one",1,password1
name2,2,password2';

        $delimiter = ',';
        $fieldEnclosure = '"';

        $expectedCsvHeaders = [
            'name' => 'name',
            'age' => 'age',
            'password' => 'password',
        ];
        $expectedCsvArray[] = [
            'name' => 'name one',
            'age' => '1',
            'password' => 'password1'
        ];
        $expectedCsvArray[] = [
            'name' => 'name2',
            'age' => '2',
            'password' => 'password2'
        ];

        $file = $this->getMock(TYPO3\CMS\Core\Resource\File::class, ['getContents'], [], '', false);
        $file->expects($this->any())->method('getContents')->will($this->returnValue($csvContent));

        $originalResource = $this->getMock(TYPO3\CMS\Core\Resource\File::class, ['getOriginalFile'], [], '', false);
        $originalResource->expects($this->any())->method('getOriginalFile')->will($this->returnValue($file));

        $fileReference = $this->getMock(
            TYPO3\CMS\Extbase\Domain\Model\FileReference::class,
            ['getOriginalResource'],
            [],
            '',
            false
        );
        $fileReference->expects(
            $this->any()
        )->method('getOriginalResource')->will($this->returnValue($originalResource));

        $import->expects($this->any())->method('getFile')->will($this->returnValue($fileReference));
        $import->expects($this->any())->method('getDelimeter')->will($this->returnValue($delimiter));
        $import->expects($this->any())->method('getEnclosure')->will($this->returnValue($fieldEnclosure));

        // Fake fe_users TCA columns
        $feUserColumns = [
            'username' => [],
            'pid' => [],
            'uid' => [],
            'password' => [],
        ];
        $GLOBALS['TCA']['fe_users']['columns'] = $feUserColumns;

        // Typescript settings
        $typoscriptSettings = [
            'allowedTables' => 'table1, table2, table3',
        ];
        $expectedAllowedTables = [
            'table1',
            'table2',
            'table3',
        ];
        $this->inject($this->subject, 'settings', $typoscriptSettings);

        $view = $this->getMockBuilder(
            \TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class
        )->getMock();
        $this->inject($this->subject, 'view', $view);

        $view->expects($this->at(0))->method('assign')->with('import', $import);
        $view->expects($this->at(1))->method('assign')->with(
            'fe_users_columns',
            array_combine(array_keys($feUserColumns), array_keys($feUserColumns))
        );
        $view->expects($this->at(2))->method('assign')->with('allowedTables', $expectedAllowedTables);
        $view->expects($this->at(3))->method('assign')->with(
            'csvHeader',
            array_combine(array_keys($expectedCsvHeaders), array_keys($expectedCsvHeaders))
        );
        $view->expects($this->at(4))->method('assign')->with('csvArray', $expectedCsvArray);
        $this->subject->showAction($import);
    }

    /**
     * @fest
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

    /**
     * @test
     */
    public function getTableColumnNameActionTest()
    {
        // Fake fe_users TCA columns
        $feUserColumns = [
            'username' => [],
            'pid' => [],
            'uid' => [],
            'password' => [],
        ];
        $GLOBALS['TCA']['fe_users']['columns'] = $feUserColumns;
        $data['table']='fe_users';
        $request = $this->getMock(\TYPO3\CMS\Core\Http\ServerRequest::class, ['getParsedBody'], [], '', false);
        $request->expects($this->any())->method('getParsedBody')->will($this->returnValue($data));

        $write = $this->getMock(\TYPO3\CMS\Core\Http\Stream::class, ['write'], [], '', false);
        $write->expects($this->any())->method('write')->will($this->returnValue('test'));
/*
        $body = $this->getMock(\TYPO3\CMS\Core\Http\Message::class, ['getBody'], [], '', false);
        $body->expects($this->any())->method('getBody')->will($this->returnValue($write));
*/
        $response = $this->getMock(\TYPO3\CMS\Core\Http\Response::class, ['getBody'], [], '', false);
        $response->expects($this->any())->method('getBody')->will($this->returnValue($write));

        $this->subject->getTableColumnNameAction($request, $response);
        /*
        $data = $request->getParsedBody();
        $column_names = array_keys($GLOBALS['TCA'][$data['table']]['columns']);
        $json = json_encode($column_names);
        $response->getBody()->write($json);
        return $response;
        */
    }
}
