<?php
namespace Pixelant\Importify\Tests\Functional\Controller;

/**
 * Test case.
 */
class ImportControllerTest extends \Nimut\TestingFramework\TestCase\FunctionalTestCase
{
    protected $subject = null;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = array(
        'typo3conf/ext/importify',
    );

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
    public function importFileActionTest()
    {
        // count the rows in fe_users
        $rowStart= $this->getDatabaseConnection()->exec_SELECTcountRows('*', 'fe_users');

        // Fake fe_users TCA columns
        $feUserColumns = [
            'username' => [],
            'pid' => [],
            'uid' => [],
            'password' => [],
        ];
        $GLOBALS['TCA']['fe_users']['columns'] = $feUserColumns;
        $data['table'] = 'fe_users';

        // test fail import, get error message
        $importData['0'] = [
            'uid' => '1',
            'username' => 'testuser',
            'password' =>
            '$pbkdf2-sha256$25000$q2OzDa0Igq9OxuMmQqDkJwzDa0$4LulX3ZwQ56aZjHNrGWus52zDa0ld3KgQrdzym60zDa0W/xonXQpbk',
            'usergroup' => '1',
            'starttime' => 'Hellokitty',
        ];
        $data['importData'] = $importData;


        $request = $this->getMock(\TYPO3\CMS\Core\Http\ServerRequest::class, ['getParsedBody'], [], '', false);
        $request->expects($this->any())->method('getParsedBody')->will($this->returnValue($data));
        $general = $this->getMock(\TYPO3\CMS\Core\Utility\GeneralUtility::class, ['makeInstance'], [], '', false);
        $schema = $this->getMock(\Doctrine\DBAL\Schema\MySqlSchemaManager::class, [], [], '', false);
        $conn = $this->getMock(
            \TYPO3\CMS\Core\Database\ConnectionPool::class,
            ['getConnectionForTable'],
            [],
            '',
            false
        );

        $error['0-3'] ='invalid Type Integer for column starttime';
        $connection = $this->getMock(\TYPO3\CMS\Core\Database\Connection::class, ['getSchemaManager'], [], '', false);

        $general->expects($this->any())->method('makeInstance')->will($this->returnValue($conn));
        $connection->expects($this->any())->method('getSchemaManager')->will($this->returnValue($schema));
        $conn->expects($this->any())->method('getConnectionForTable')
        ->with($data['table'])->will($this->returnValue($connection));

        $write = $this->getMock(\TYPO3\CMS\Core\Http\Stream::class, ['write'], [], '', false);
        $write->expects($this->any())->method('write')->will($this->returnValue(json_encode(['error' => $error])));

        $response = $this->getMock(\TYPO3\CMS\Core\Http\Response::class, ['getBody'], [], '', false);
        $response->expects($this->any())->method('getBody')->will($this->returnValue($write));
        $this->subject->importFileAction($request, $response);

        $rowAfterErrorInsert= $this->getDatabaseConnection()->exec_SELECTcountRows('*', 'fe_users');

        // test not inserted when there are errors
        $this->assertSame($rowStart, $rowAfterErrorInsert);
        $dbData= $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
            'uid, username, password,usergroup, name',
            'fe_users',
            'uid=1'
        );
        // test not inserted
        $this->assertNotSame($dbData, $importData);

        // test successful import
        $importData['0'] = [
            'uid' => '2',
            'username' => 'testuser',
            'password' =>
            '$pbkdf2-sha256$25000$q2OzDa0Igq9OxuMmQqDkJw$4LulX3ZwQ56aZjHNrGWus52ld3KgQrdzym60W/xonXQ',
            'usergroup' => '1',
            'name' => 'Hellokitty',
        ];
        $data['importData'] = $importData;

        $request = $this->getMock(\TYPO3\CMS\Core\Http\ServerRequest::class, ['getParsedBody'], [], '', false);
        $request->expects($this->any())->method('getParsedBody')->will($this->returnValue($data));
        $write->expects($this->any())->method('write')->will($this->returnValue(json_encode(['error' => null])));
        $response->expects($this->any())->method('getBody')->will($this->returnValue($write));
        $this->subject->importFileAction($request, $response);

        // test only one successful insert
        $rowAfterSuccessInsert= $this->getDatabaseConnection()->exec_SELECTcountRows('*', 'fe_users');
        $this->assertSame($rowStart+1, $rowAfterSuccessInsert);
        $dbData= $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
            'uid, username, password,usergroup, name',
            'fe_users',
            'uid=2'
        );

        // test successful insert, inserted data and selected data in database is the same
        $this->assertSame($dbData, $importData['0']);
    }
}
