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
            const MAX_TINYINT_UNSIGNED = 255;
            const MAX_SMALLINT_UNSIGNED = 65535;
            const MAX_MEDIUMINT_UNSIGNED = 16777215;
            const MAX_INT_UNSIGNED = 4294967295;
            const MAX_BIGINT_UNSIGNED = 18446744073709551615;
            const MAX_DECIMAL_UNSIGNED = 2*10**38;
            const MIN_UNSIGNED = 0;

            const MAX_TINYINT_SIGNED = 127;
            const MAX_SMALLINT_SIGNED = 32767;
            const MAX_MEDIUMINT_SIGNED = 8388607;
            const MAX_INT_SIGNED = 2147483647;
            const MAX_BIGINT_SIGNED = 9223372036854775807;
            const MAX_FLOAT_SIGNED = 3.402823466E+38;
            const MAX_DOUBLE_SIGNED = 1.79E+308;
            const MAX_DECIMAL_SIGNED = 10**38-1;

            const MIN_TINYINT_SIGNED = -128;
            const MIN_SMALLINT_SIGNED = -32768;
            const MIN_MEDIUMINT_SIGNED = -8388608;
            const MIN_INT_SIGNED = -2147483648;
            const MIN_BIGINT_SIGNED = -9223372036854775808;
            const MIN_FLOAT_SIGNED = -3.402823466E+38;
            const MIN_DOUBLE_SIGNED = -1.79E+308;
            const MIN_DECIMAL_SIGNED = -10**38+1;

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
            $this->addFlashMessage('Your new Import was created!');
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
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($data['table']);
        $queryBuilder->insert($data['table']);
        $error = null;
        foreach ($importData as $data) {
            foreach ($data as $key => $value) {
                $key = strtolower($key);
                $value = str_replace('−','-',$value);
                $invalid = $this->invalidInput($columns[$key], $value,$key);
                if (!empty($invalid)) {
                    $error[] = $invalid;
                    unset($data[$key]);
                }
            }
            $queryBuilder->values($data)->execute();
        }
        $response->getBody()->write(json_encode(['error' => $error]));
        return $response;
    }

    public function invalidInput($column, $input, $columnName){
        $error = [];
        $typeIsString=$this->isDatabaseTypeString($column->getType());
        $typeIsInt=$this->isDatabaseTypeInt($column->getType());
        $dbStructureIsUnsigned=$column->getUnsigned();
        $inputIsNumber=is_numeric(floor($input));
        $inputIsString=is_string($input);
        $inputIsUnsigned=ctype_digit($input);

        if (!($typeIsString && $inputIsString || $typeIsInt && $inputIsNumber)) {
            $error[]= "Invalid data \'".$input."\' for type numeric for column \'" . $columnName."\'";
        } elseif ($typeIsInt && $dbStructureIsUnsigned && !$inputIsUnsigned) {
            $error[] = "Data \'".$input."\', Not Unsigned column \'" .$columnName."\'";
        } elseif ($this->invalidInputLenght($column->getType(), $dbStructureIsUnsigned, $column->getLength(), $input)) {
            $error[] = "Data \'".$input."\' too long for column \'" .$columnName."\' length: " . $column->getLength().', input length: '.strlen($input);
        }
        return $error;
    }

    protected function isDatabaseTypeString($type) {
        $type = strtoupper($type);
        return $type == 'CHAR' ||
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
            $type == 'SET';
    }

    protected function isDatabaseTypeInt($type) {
        $type = strtoupper($type);
        return $type == 'TINYINT' ||
            $type == 'SMALLINT' ||
            $type == 'MEDIUMINT' ||
            $type == 'INT' ||
            $type == 'INTEGER' ||
            $type == 'BIGINT' ||
            $type == 'FLOAT' ||
            $type == 'DOUBLE' ||
            $type == 'DECIMAL';
    }

    public function invalidInputLenght($type, $databaseIsUnsigned, $length, $input){
        $typeIsString=$this->isDatabaseTypeString($type);
        $typeIsInt=$this->isDatabaseTypeInt($type);
        $inputIsUnsigned=ctype_digit($input);

        if($typeIsString){
            return $length < strlen($input);
        } elseif($typeIsInt){
            $type = strtoupper($type);
            if ($inputIsUnsigned && $databaseIsUnsigned) {
                switch ($type) {
                    case "TINYINT":
                        return self::MAX_TINYINT_UNSIGNED<$input;
                    case "SMALLINT":
                        return self::MAX_SMALLINT_UNSIGNED<$input;
                    case "MEDIUMINT":
                        return self::MAX_SMALLINT_UNSIGNED<$input;
                    case "INT":
                    case "INTEGER":
                        return self::MAX_INT_UNSIGNED<$input;
                    case "BIGINT":
                        return self::MAX_BIGINT_UNSIGNED<$input;
                    case "DECIMAL":
                        return self::MAX_DECIMAL_UNSIGNED<$input;
                    default:
                        echo "UNSIGNED DEFAULT ERROR!!";
                }
            } else {
                switch ($type) {
                    case "TINYINT":
                        return self::MAX_TINYINT_SIGNED<$input || self::MIN_TINYINT_SIGNED>$input;
                    case "SMALLINT":
                        return self::MAX_SMALLINT_SIGNED<$input || self::MIN_SMALLINT_SIGNED>$input;
                    case "MEDIUMINT":
                        return self::MAX_SMALLINT_SIGNED<$input || self::MIN_SMALLINT_SIGNED>$input;
                    case "INT":
                    case "INTEGER":
                        return self::MAX_INT_SIGNED<$input || self::MIN_INT_SIGNED>$input;
                    case "BIGINT":
                        return self::MAX_BIGINT_SIGNED<$input || self::MIN_BIGINT_SIGNED>$input;
                    case "FLOAT":
                        return self::MAX_FLOAT_SIGNED<$input || self::MIN_FLOAT_SIGNED>$input;
                    case "DOUBLE":
                        return self::MAX_DOUBLE_SIGNED<$input || self::MIN_DOUBLE_SIGNED>$input;
                    case "DECIMAL":
                        return self::MAX_DECIMAL_SIGNED<$input || self::MIN_DECIMAL_SIGNED>$input;
                    default:
                        echo "SIGNED ERROR DEFAULT!!";
                }
            }
        }
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
