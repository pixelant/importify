<?php
namespace Pixelant\Importify\Controller;

use Pixelant\Importify\Property\TypeConverter\UploadedFileReferenceConverter;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
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
        $this->setTypeConverterConfigurationForFileUpload('import');
    }

    /**
     * Set TypeConverter option for image upload
     */
    public function initializeCreateAction()
    {
        $this->setTypeConverterConfigurationForFileUpload('newImport');
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
            $persistenceManager = $this->objectManager->get(
                \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class
            );
            $persistenceManager->persistAll();
            $this->addFlashMessage('The object was created');
            $this->redirect('show', 'Import', 'Importify', ['import' => $newImport]);
        }
    }

    /**
     * action upload
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
        $this->addFlashMessage('The object was updated');
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
        $this->addFlashMessage('The object was deleted');
        $this->importRepository->remove($import);
        $this->redirect('list');
    }

    /**
     * Get the ajax request and return response with column name
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface $response
     */
    public function getTableColumnNameAction(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response
    ) {
    
        $data = $request->getParsedBody();
        $column_names = array_keys($GLOBALS['TCA'][$data['table']]['columns']);
        $json = json_encode($column_names);
        $response->getBody()->write($json);
        return $response;
    }

    /**
     * Validate and insert data to database
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface $response
     */
    public function importFileAction(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response
    ) {
        $data = $request->getParsedBody();
        $importData = $data['importData'];
        $connePool = GeneralUtility::makeInstance(ConnectionPool::class);
        $sm = $connePool->getConnectionForTable($data['table'])->getSchemaManager();
        $columns = $sm->listTableColumns($data['table']);
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($data['table']);
        $queryBuilder->insert($data['table']);
        $error = null;
        $row = 0;

        // validation, add row, column and error message to $error if found invalid input data
        foreach ($importData as $data) {
            $col = 0;
            foreach ($data as $key => $value) {
                $keyLowerCase = strtolower($key);
                $value = str_replace('−', '-', $value);
                $invalid = $this->invalidInput($columns[$keyLowerCase], $value, $keyLowerCase);
                if ($invalid) {
                    $error[$row][$col][] = $invalid;
                }
                $col++;
            }
            $row++;
        }

        // insert if $error is not changed
        if ($error === null) {
            foreach ($importData as $data) {
                foreach ($data as $key => $value) {
                    if ($invalid) {
                        unset($data[$key]);
                    }
                }
                $queryBuilder->values($data)->execute();
            }
        }
        $response->getBody()->write(json_encode(['error' => $error]));
        return $response;
    }

    /**
     * Validate and insert data to database
     *
     * @param \Doctrine\DBAL\Schema\Column $column
     * @param string $input
     * @param string $columnName
     * @return string $error
     */
    public function invalidInput($column, $input, $columnName)
    {
        $typeIsString = $this->isDatabaseTypeString($column->getType());
        $typeIsNumeric = $this->isDatabaseTypeNumeric($column->getType());
        $dbStructureIsUnsigned = $column->getUnsigned();
        $inputIsNumeric = is_numeric($input);
        $inputIsString = is_string($input);
        $inputIsUnsigned = ctype_digit($input);

        if (!($typeIsString && $inputIsString || $typeIsNumeric && $inputIsNumeric)) {
            $error = 'Invalid data:' . $input . ', for type ' . $column->getType() . ' for column ' . $columnName;
        } elseif ($typeIsNumeric && $dbStructureIsUnsigned && !$inputIsUnsigned && $inputIsNumeric) {
            $error = 'Data:' . $input . ', Not Unsigned column: ' . $columnName;
        } elseif ($this->invalidInputLenght($column->getType(), $dbStructureIsUnsigned, $column->getLength(), $input)) {
            $error = 'Data:' . $input . ', not allowed length for column:' . $columnName;
        }
        return $error;
    }

    /**
     * Validate if database column type is string
     *
     * @param \Doctrine\DBAL\Types\Type $type
     * @return boolean
     */
    protected function isDatabaseTypeString($type)
    {
        $type = strtoupper($type);
        return $type === 'CHAR' ||
            $type === 'VARCHAR' ||
            $type === 'TINYTEXT' ||
            $type === 'BLOB' ||
            $type === 'MEDIUMTEXT' ||
            $type === 'MEDIUMBLOB' ||
            $type === 'LONGTEXT' ||
            $type === 'LONGBLOB' ||
            $type === 'ENUM' ||
            $type === 'TEXT'||
            $type === 'STRING'||
            $type === 'SET';
    }

    /**
     * Validate if database column type is numeric
     *
     * @param \Doctrine\DBAL\Types\Type $type
     * @return boolean
     */
    protected function isDatabaseTypeNumeric($type)
    {
        $type = strtoupper($type);
        return $type === 'TINYINT' ||
            $type === 'SMALLINT' ||
            $type === 'MEDIUMINT' ||
            $type === 'INT' ||
            $type === 'INTEGER' ||
            $type === 'BIGINT' ||
            $type === 'FLOAT' ||
            $type === 'DOUBLE' ||
            $type === 'DECIMAL';
    }

    /**
     * Validate if database column type is numeric
     *
     * @param \Doctrine\DBAL\Types\Type $type
     * @param boolean $databaseIsUnsigned
     * @param integer|null $length
     * @param string $input
     * @return boolean
     */
    public function invalidInputLenght($type, $databaseIsUnsigned, $length, $input)
    {
        $typeIsString=$this->isDatabaseTypeString($type);
        $typeIsNumeric=$this->isDatabaseTypeNumeric($type);
        $inputIsUnsigned=ctype_digit($input);

        if ($typeIsString) {
            return $length < strlen($input);
        } elseif ($typeIsNumeric) {
            $type = strtoupper($type);
            if ($inputIsUnsigned && $databaseIsUnsigned) {
                switch ($type) {
                    case 'TINYINT':
                        return self::MAX_TINYINT_UNSIGNED < $input;
                    case 'SMALLINT':
                        return self::MAX_SMALLINT_UNSIGNED < $input;
                    case 'MEDIUMINT':
                        return self::MAX_SMALLINT_UNSIGNED < $input;
                    case 'INT':
                    case 'INTEGER':
                        return self::MAX_INT_UNSIGNED < $input;
                    case 'BIGINT':
                        return self::MAX_BIGINT_UNSIGNED < $input;
                    case 'DECIMAL':
                        return self::MAX_DECIMAL_UNSIGNED < $input;
                    default:
                        echo 'UNSIGNED DEFAULT ERROR!!';
                }
            } else {
                switch ($type) {
                    case 'TINYINT':
                        return self::MAX_TINYINT_SIGNED < $input || self::MIN_TINYINT_SIGNED > $input;
                    case 'SMALLINT':
                        return self::MAX_SMALLINT_SIGNED < $input || self::MIN_SMALLINT_SIGNED > $input;
                    case 'MEDIUMINT':
                        return self::MAX_SMALLINT_SIGNED < $input || self::MIN_SMALLINT_SIGNED > $input;
                    case 'INT':
                    case 'INTEGER':
                        return self::MAX_INT_SIGNED < $input || self::MIN_INT_SIGNED > $input;
                    case 'BIGINT':
                        return self::MAX_BIGINT_SIGNED < $input || self::MIN_BIGINT_SIGNED > $input;
                    case 'FLOAT':
                        return self::MAX_FLOAT_SIGNED < $input || self::MIN_FLOAT_SIGNED > $input;
                    case 'DOUBLE':
                        return self::MAX_DOUBLE_SIGNED < $input || self::MIN_DOUBLE_SIGNED > $input;
                    case 'DECIMAL':
                        return self::MAX_DECIMAL_SIGNED < $input || self::MIN_DECIMAL_SIGNED > $input;
                    default:
                        echo 'SIGNED ERROR DEFAULT!!';
                }
            }
        }
    }

    /**
     * File Upload Converter Configuration
     *
     * @param $argumentName
     */
    protected function setTypeConverterConfigurationForFileUpload($argumentName)
    {
        $uploadConfiguration = [
            UploadedFileReferenceConverter::CONFIGURATION_ALLOWED_FILE_EXTENSIONS => 'csv,txt',
            UploadedFileReferenceConverter::CONFIGURATION_UPLOAD_FOLDER => '1:/user_upload/'
        ];
        $newImportConfiguration = $this->arguments[$argumentName]->getPropertyMappingConfiguration();
        $newImportConfiguration->forProperty('file')->setTypeConverterOptions(
            'Pixelant\\Importify\\Property\\TypeConverter\\UploadedFileReferenceConverter',
            $uploadConfiguration
        );
    }
}
