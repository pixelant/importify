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
        $fe_users_columns = array_keys($GLOBALS['TCA']['fe_users']['columns']);
        $this->view->assign('fe_users_columns', $fe_users_columns);
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

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($data['table']);$queryBuilder->insert($data['table']);
        $error = null;
        foreach ($importData as $data) {
            var_dump($data);
            foreach ($data as $key => $value) {
                $key = strtolower($key);
                $value = str_replace('−','-',$value);
                $error = $this->validateInput($columns[$key], $value,$key);
                if ($error){
                    unset($data[$key]);
                }
            }
            $queryBuilder->values($data)->execute();
        }
        $response->getBody()->write(json_encode(['success' => 1],['error' => $error]));
        return $response;
    }

    public function validateInput($column, $input, $columnName){
        var_dump($input);
        $error = [];
        $res = $this->validateInputTypeAndUnsigned((string)$column->getType(),$column->getUnsigned(),$input);
        if (!$res['type']){
            $error['type'] = "Invalid data for type numeric for column \'" . $columnName."\'";
        } elseif (!$res['unsigned'] && $res['unsigned'] !==null){
            $error['unsigned'] = "Not Unsigned column \'" .$columnName."\'";
        } elseif (!$this->validateInputNotNull($column->getNotNull(),$input)){
            $error['null'] = "Null column not allowed \'" .$columnName;
        } elseif (!$this->validateInputLenght($column->getLength(),$input)){
            $error['length'] = "Data too long for column \'" .$columnName."\'";
        }
        print_r($error);
        return $error;
    }
    public function validateInputLenght($length, $input){
        return $length >= strlen($input);
    }
    public function validateInputUnsigned($unsigned, $input){
        return $unsigned && ctype_digit($input);
    }
    public function validateInputNotNull($notnull, $input){
        return $notnull && isset($input);
    }
    public function validateInputTypeAndUnsigned($type,$unsigned, $input){
        $res['unsigned'] = null;
        $res['type'] = false;
        $type=strtoupper($type);
        // check if column type and input type. is int
        // or if column type and input type is string
        if (($type == 'TINYINT' ||
            $type == 'SMALLINT' ||
            $type == 'MEDIUMINT' ||
            $type == 'INT' ||
            $type == 'BIGINT' ||
            $type == 'FLOAT' ||
            $type == 'DOUBLE' ||
            $type == 'DECIMAL' ||
            $type == 'INTEGER') &&
            is_numeric(floor($input))
        ){
            $res['type'] = true;
            $res['unsigned']=$this->validateInputUnsigned($unsigned,$input);
            return $res;
        } elseif (($type == 'CHAR' ||
            $type == 'VARCHAR' ||
            $type == 'TINYTEXT' ||
            $type == 'BLOB' ||
            $type == 'MEDIUMTEXT' ||
            $type == 'MEDIUMBLOB' ||
            $type == 'LONGTEXT' ||
            $type == 'LONGBLOB' ||
            $type == 'ENUM' ||
            $type == 'TEXT'||
            $type == 'STRING'||
            $type == 'SET') &&
            is_string($input)
        ){
            $res['type'] = true;
            return $res;
        }
        return $res;
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
