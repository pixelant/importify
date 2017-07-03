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
    public function initializeCreateActionTest()
    {
        $argument = $this->getMock(
            \TYPO3\CMS\Extbase\Mvc\Controller\Argument::class,
            ['getPropertyMappingConfiguration'],
            [],
            '',
            false
        );

        $propertyFile = $this->getMock(
            \TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration::class,
            ['setTypeConverterOptions'],
            [],
            '',
            false
        );
        $arguments['newImport'] = $argument;
        $this->inject($this->subject, 'arguments', $arguments);
        $arguments['newImport']->expects(self::once())->method(
            'getPropertyMappingConfiguration'
        )->will($this->returnValue($propertyFile));

        $this->subject->initializeCreateAction();
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

        $import = $this->getMock(
            \Pixelant\Importify\Domain\Model\Import::class,
            ['getFile', 'getDelimeter', 'getEnclosure'],
            [],
            '',
            false
        );

        $file = $this->getMock(\TYPO3\CMS\Core\Resource\File::class, ['getContents'], [], '', false);
        $file->expects($this->once())->method('getContents')->will($this->returnValue($csvContent));

        $originalResource = $this->getMock(\TYPO3\CMS\Core\Resource\File::class, ['getOriginalFile'], [], '', false);
        $originalResource->expects($this->once())->method('getOriginalFile')->will($this->returnValue($file));

        $fileReference = $this->getMock(
            \TYPO3\CMS\Extbase\Domain\Model\FileReference::class,
            ['getOriginalResource'],
            [],
            '',
            false
        );
        $fileReference->expects(
            $this->once()
        )->method('getOriginalResource')->will($this->returnValue($originalResource));

        $import->expects($this->once())->method('getFile')->will($this->returnValue($fileReference));
        $import->expects($this->once())->method('getDelimeter')->will($this->returnValue($delimiter));
        $import->expects($this->once())->method('getEnclosure')->will($this->returnValue($fieldEnclosure));

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
     * @test
     */
    public function newActionAssignsTheGivenImportToView()
    {
        $newImport = new \Pixelant\Importify\Domain\Model\Import();

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $this->inject($this->subject, 'view', $view);
        $view->expects(self::once())->method('assign')->with('newImport', $newImport);

        $this->subject->newAction($newImport);
    }

    /**
     * @test
     */
    public function createActionTest()
    {
        $importNoFile = $this->getMock(
            \Pixelant\Importify\Domain\Model\Import::class,
            [],
            [],
            '',
            false
        );
        $importNoFile->expects($this->once())->method('getFile')->will($this->returnValue(null));

        $import = $this->getMock(
            \Pixelant\Importify\Domain\Model\Import::class,
            ['getFile'],
            [],
            '',
            false
        );
        $import->expects($this->once())->method('getFile')->will($this->returnValue('test'));

        $importRepository = $this->getMockBuilder(\Pixelant\Importify\Domain\Repository\ImportRepository::class)
            ->setMethods(['add'])
            ->disableOriginalConstructor()
            ->getMock();
        $importRepository->expects(self::once())->method('add')->with($import);
        $this->inject($this->subject, 'importRepository', $importRepository);

        $persistenceManager = $this->getMockBuilder(
            \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class
        )->getMock();

        $objectManager = $this->getMockBuilder(\TYPO3\CMS\Extbase\Object\ObjectManager::class)->getMock();
        $this->inject($this->subject, 'objectManager', $objectManager);
        $objectManager->expects(self::once())->method('get')->with(
            \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class
        )->will($this->returnValue($persistenceManager));

        $persistenceManager->expects(self::once())->method('persistAll')->will($this->returnValue(true));

        $this->subject->createAction($import);
        $this->subject->createAction($importNoFile);
    }

    /**
     * @test
     */
    public function uploadActionTest()
    {
        $this->subject->uploadAction('test');
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
        $request->expects($this->once())->method('getParsedBody')->will($this->returnValue($data));

        $write = $this->getMock(\TYPO3\CMS\Core\Http\Stream::class, ['write'], [], '', false);
        $write->expects($this->once())->method('write')->will($this->returnValue('test'));

        $response = $this->getMock(\TYPO3\CMS\Core\Http\Response::class, ['getBody'], [], '', false);
        $response->expects($this->once())->method('getBody')->will($this->returnValue($write));

        $this->subject->getTableColumnNameAction($request, $response);
    }

    /**
     * @test
     */
    public function invalidInputTest()
    {
        $column = $this->getMock(
            \Doctrine\DBAL\Schema\Column::class,
            ['getType','getLength','getUnsigned'],
            [],
            '',
            false
        );

        // test invalid input string and column type int
        $column->expects($this->any())->method('getType')->will($this->returnValue('int'));
        self::assertFalse(is_null($this->subject->invalidInput($column, 'test', 'lastlogin')));

        // test invalid input signed int and column type unsigned int
        $column->expects($this->any())->method('getUnsigned')->will($this->returnValue('1'));
        self::assertFalse(is_null($this->subject->invalidInput($column, '-1', 'lastlogin')));

        // test invalid input unsigned int length and column length 4294967295
        $column->expects($this->once())->method('getLength')->will($this->returnValue('10'));
        self::assertFalse(is_null($this->subject->invalidInput($column, '4294967296', 'lastlogin')));

        // test invalid input string length and column string
        $column->expects($this->any())->method('getType')->will($this->returnValue('string'));
        self::assertFalse(is_null($this->subject->invalidInput($column, 'helloworlds', 'username')));
    }

    /**
     * @test
     */
    public function invalidInputLenghtTest()
    {
        // test too long string input
        self::assertTrue($this->subject->invalidInputLenght('string', '0', '4', 'hello'));

        // test too short numeric input for signed column
        self::assertTrue($this->subject->invalidInputLenght('tinyint', '0', '10', '-129'));
        self::assertTrue($this->subject->invalidInputLenght('smallint', '0', '10', '-32769'));
        self::assertTrue($this->subject->invalidInputLenght('mediumint', '0', '10', '-8388609'));
        self::assertTrue($this->subject->invalidInputLenght('int', '0', '10', '-2147483649'));
        self::assertTrue($this->subject->invalidInputLenght('integer', '0', '10', '-2147483649'));
        self::assertTrue($this->subject->invalidInputLenght('bigint', '0', '10', '-9.2233720368549E+18'));
        self::assertTrue($this->subject->invalidInputLenght('float', '0', '10', '-3.4028234661E+38'));
        self::assertTrue($this->subject->invalidInputLenght('double', '0', '10', '-1.791E+308'));
        self::assertTrue($this->subject->invalidInputLenght('decimal', '0', '10', '-1.0001E+38'));

        // test too long numeric input for signed column
        self::assertTrue($this->subject->invalidInputLenght('tinyint', '0', '10', '128'));
        self::assertTrue($this->subject->invalidInputLenght('smallint', '0', '10', '32768'));
        self::assertTrue($this->subject->invalidInputLenght('mediumint', '0', '10', '8388608'));
        self::assertTrue($this->subject->invalidInputLenght('int', '0', '10', '2147483648'));
        self::assertTrue($this->subject->invalidInputLenght('integer', '0', '10', '2147483648'));
        self::assertTrue($this->subject->invalidInputLenght('bigint', '0', '10', '9.2233720368549E+18'));
        self::assertTrue($this->subject->invalidInputLenght('float', '0', '10', '3.4028234661E+38'));
        self::assertTrue($this->subject->invalidInputLenght('double', '0', '10', '1.791E+308'));
        self::assertTrue($this->subject->invalidInputLenght('decimal', '0', '10', '1.0001E+38'));

        // test too long numeric input for unsigned column
        self::assertTrue($this->subject->invalidInputLenght('tinyint', '1', '10', '256'));
        self::assertTrue($this->subject->invalidInputLenght('smallint', '1', '10', '65536'));
        self::assertTrue($this->subject->invalidInputLenght('mediumint', '1', '10', '16777216'));
        self::assertTrue($this->subject->invalidInputLenght('int', '1', '10', '4294967296'));
        self::assertTrue($this->subject->invalidInputLenght('integer', '1', '10', '4294967296'));
        self::assertTrue($this->subject->invalidInputLenght('float', '1', '10', '3.4028234661E+38'));
        self::assertTrue($this->subject->invalidInputLenght('double', '1', '10', '1.791E+308'));

        // numeric length too long, test if valid input instead, or need to convert to float
        self::assertFalse($this->subject->invalidInputLenght('bigint', '1', '10', '999999999'));
        self::assertFalse($this->subject->invalidInputLenght('decimal', '1', '10', '999999999'));

        // test int input for unsigned column
        self::assertTrue($this->subject->invalidInputLenght('tinyint', '1', '10', '-1'));
        self::assertTrue($this->subject->invalidInputLenght('smallint', '1', '10', '-1'));
        self::assertTrue($this->subject->invalidInputLenght('mediumint', '1', '10', '-1'));
        self::assertTrue($this->subject->invalidInputLenght('int', '1', '10', '-1'));
        self::assertTrue($this->subject->invalidInputLenght('integer', '1', '10', '-1'));
        self::assertTrue($this->subject->invalidInputLenght('bigint', '1', '10', '-1'));
        self::assertTrue($this->subject->invalidInputLenght('float', '1', '10', '-1.1'));
        self::assertTrue($this->subject->invalidInputLenght('double', '1', '10', '-1.1'));
        self::assertTrue($this->subject->invalidInputLenght('decimal', '1', '10', '-1'));

        // test wrong type
        self::assertNull($this->subject->invalidInputLenght('test', '1', '10', '1'));
        self::assertNull($this->subject->invalidInputLenght('tetst', '0', '10', '1'));
    }
}
