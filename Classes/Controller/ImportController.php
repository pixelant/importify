<?php
namespace Pixelant\Importify\Controller;

use Pixelant\Importify\Property\TypeConverter\UploadedFileReferenceConverter;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/***
 *
 * This file is part of the "Import" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2017 Tim Ta <tim.ta@pixelant.se>, Pixelant
 *
 ***/

/**
 * ImportController
 */
class ImportController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * importRepository
     *
     * @var \Pixelant\Importify\Domain\Repository\ImportRepository
     * @inject
     */
    protected $importRepository = null;

    /**
     * Set TypeConverter option for image upload
     */
    public function initializeUpdateAction()
    {
        $this->setTypeConverterConfigurationForImageUpload('import');
    }

    /**
     * Set TypeConverter option for image upload
     */
    public function initializeCreateAction()
    {
        $this->setTypeConverterConfigurationForImageUpload('newImport');
    }

    /**
     * action list
     *
     * @param Pixelant\Importify\Domain\Model\Import
     * @return void
     */
    public function listAction()
    {
        $imports = $this->importRepository->findAll();
        $this->view->assign('imports', $imports);
    }

    /**
     * action show
     *
     * @param Pixelant\Importify\Domain\Model\Import
     * @return void
     */
    public function showAction(\Pixelant\Importify\Domain\Model\Import $import)
    {
        $this->view->assign('import', $import);
        $column_names = array_keys($GLOBALS['TCA']['fe_users']['columns']);
        $this->view->assign('fe_users_columns', $column_names);
        $file = $import->getFile()->getOriginalResource()->getOriginalFile();
        $content = $file->getContents();
        $delimeter = $import->getDelimeter();
        $fieldEnclosure = $import->getEnclosure();
        $csvArray = \TYPO3\CMS\Core\Utility\CsvUtility::csvToArray($content, $delimeter, $fieldEnclosure);
        $allowedTables = GeneralUtility::trimExplode(',', $this->settings['allowedTables']);
        $csvHeader = array_shift($csvArray);
        $this->view->assign('allowedTables', $allowedTables);
        $this->view->assign('csvHeader', $csvHeader);
        $this->view->assign('csvArray', $csvArray);
    }

    /**
     * action new
     *
     * @param \Pixelant\Importify\Domain\Model\Import $newImport
     * @return void
     */
    public function newAction(\Pixelant\Importify\Domain\Model\Import $newImport = null)
    {
        $newImport = new \Pixelant\Importify\Domain\Model\Import();
        $this->view->assign('newImport', $newImport);

    }

    /**
     * action create
     *
     * @param \Pixelant\Importify\Domain\Model\Import $newImport
     * @return void
     */
    public function createAction(\Pixelant\Importify\Domain\Model\Import $newImport)
    {
        if (is_null($newImport->getFile())) {
            $this->addFlashMessage('File was missing!');
        } else {
            $this->importRepository->add($newImport);
            $persistenceManager = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class);
            $persistenceManager->persistAll();
            $this->addFlashMessage('Your new Import was created!');//exit;
            $this->redirect('show', 'Import', 'Importify', ['import' => $newImport]);
        }
    }

    /**
     * action edit
     *
     * @param string $identifier
     * @return void
     */
    public function uploadAction(string $identifier)
    {
    }

    /**
     * action edit
     *
     * @param \Pixelant\Importify\Domain\Model\Import $import
     * @ignorevalidation $import
     * @return void
     */
    public function editAction(\Pixelant\Importify\Domain\Model\Import $import)
    {
        $this->view->assign('import', $import);
    }

    /**
     * action update
     *
     * @param \Pixelant\Importify\Domain\Model\Import $import
     * @return void
     */
    public function updateAction(\Pixelant\Importify\Domain\Model\Import $import)
    {
        $this->addFlashMessage('The object was updated. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->importRepository->update($import);
        $this->redirect('list');
    }

    /**
     * action delete
     *
     * @param \Pixelant\Importify\Domain\Model\Import $import
     * @return void
     */
    public function deleteAction(\Pixelant\Importify\Domain\Model\Import $import)
    {
        $this->addFlashMessage('The object was deleted. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->importRepository->remove($import);
        $this->redirect('list');
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function getTableContentAction(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response)
    {
        $data = $request->getParsedBody();
        $column_names = array_keys($GLOBALS['TCA'][$data['table']]['columns']);

        $json = json_encode($column_names);
        $response->getBody()->write($json);
        return $response;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function importFileAction(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response)
    {
        $data = $request->getParsedBody();
        $importData = $data['importData'];

        $connePool = GeneralUtility::makeInstance(ConnectionPool::class);
        $sm = $connePool->getConnectionForTable($data['table'])->getSchemaManager();
        $columns = $sm->listTableColumns($data['table']);

        $columnsType =[];
        $dataType =[];
        foreach ($columns as $column) {
            $columnsType[$column->getName()] = (string)$column->getType();
        }
        echo '<pre>';
        print_r($columnsType);
        echo  '</pre>';
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($data['table']);$queryBuilder->insert($data['table']);
        $error = "";
        foreach ($importData as $data) {
            foreach ($data as $key => $value) {
                if (!$this->checkDatabaseType($columnsType[$key], $data[$key])){
                    unset($data[$key]);
                    $error = " Invalid data for type numeric";
                }
            }
            print_r($data);
            $queryBuilder->values($data)->execute();
        }
        $response->getBody()->write(json_encode(['success' => 1],['error' => $error]));
        return $response;
    }

    public function checkDatabaseType($column, $input){
        $column=strtoupper($column);
        // check if column type and input type. is int
        // or if column type and input type is string
        if (($column == 'TINYINT' || 
            $column == 'SMALLINT' || 
            $column == 'MEDIUMINT' || 
            $column == 'INT' || 
            $column == 'BIGINT' || 
            $column == 'FLOAT' || 
            $column == 'DOUBLE' || 
            $column == 'DECIMAL' ||
            $column == 'INTEGER') &&
            is_numeric($input)
        ){
            return true;
        } elseif (($column == 'CHAR' || 
            $column == 'VARCHAR' || 
            $column == 'TINYTEXT' || 
            $column == 'BLOB' || 
            $column == 'MEDIUMTEXT' || 
            $column == 'MEDIUMBLOB' || 
            $column == 'LONGTEXT' || 
            $column == 'LONGBLOB' ||
            $column == 'ENUM' ||
            $column == 'TEXT'||
            $column == 'STRING'||
            $column == 'SET') &&
            is_string($input)
        ){
            return true;
        }
        return false;
    }

    /**
     * @param $argumentName
     */
    protected function setTypeConverterConfigurationForImageUpload($argumentName)
    {
        $uploadConfiguration = [
            UploadedFileReferenceConverter::CONFIGURATION_ALLOWED_FILE_EXTENSIONS => 'csv,txt',
            UploadedFileReferenceConverter::CONFIGURATION_UPLOAD_FOLDER => '1:/user_upload/'
        ];
        $newImportConfiguration = $this->arguments[$argumentName]->getPropertyMappingConfiguration();
        $newImportConfiguration->forProperty('file')->setTypeConverterOptions('Pixelant\\Importify\\Property\\TypeConverter\\UploadedFileReferenceConverter', $uploadConfiguration);
    }
}
