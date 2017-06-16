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

        sort($column_names, SORT_NATURAL | SORT_FLAG_CASE);
        $this->view->assign('fe_users_columns', $column_names);
        $file = $import->getFile()->getOriginalResource()->getOriginalFile();
        $content = $file->getContents();
        $delimeter = ',';
        //, ;
        $fieldEnclosure = '"';
        // "
        $csvArray = \TYPO3\CMS\Core\Utility\CsvUtility::csvToArray($content, $delimeter, $fieldEnclosure);
        //DebuggerUtility::var_dump($csvArray, 'csvArray');exit;
        $allowedTables = GeneralUtility::trimExplode(',', $this->settings['allowedTables']);
        //DebuggerUtility::var_dump($csvArray, 'csvArray (innan shift)');
        //$csvHeader = $csvArray[0];
        $csvHeader = array_shift($csvArray);

        sort($csvHeader, SORT_NATURAL | SORT_FLAG_CASE);
        //DebuggerUtility::var_dump($csvHeader, 'csvHeader');
        //DebuggerUtility::var_dump($csvArray, 'csvArray(efter shift)');exit;
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
        if ($file === null || $newImport === null) {
            $newImport = new \Pixelant\Importify\Domain\Model\Import();
            $this->view->assign('newImport', $newImport);
        } else {
            $file = $newImport->getFile()->getOriginalResource()->getOriginalFile();
            $fileContent = $file->getContents();
            $this->view->assign('fileContent', $fileContent);
            $this->view->assign('newImport', $newImport);
        }
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
            /*
                        $file = $import->getFile()->getOriginalResource()->getOriginalFile();
                        $content = $file->getContents();
                        $array = $file->toArray();

                        DebuggerUtility::var_dump($array, 'array');exit;*/

            $this->importRepository->add($newImport);
            var_dump($newImport->getUid());
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
        $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
        $file = $resourceFactory->getFileObjectFromCombinedIdentifier('1:' . $identifier);
        $content = $file->getContents();
        $delimeter = ',';
        //, ;
        $fieldEnclosure = '"';
        // "
        $csvArray = \TYPO3\CMS\Core\Utility\CsvUtility::csvToArray($content, $delimeter, $fieldEnclosure);
        $this->view->assign('csvArray', $csvArray);
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
        
        sort($column_names, SORT_NATURAL | SORT_FLAG_CASE);
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
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
        $queryBuilder->insert('fe_users');
        echo '<pre>';
        print_r($importData);
        echo '</pre>';
        
        foreach ($importData as $data) {
            $queryBuilder->values($data)->execute();
        }

        $response->getBody()->write(json_encode(['success' => 1]));
        return $response;
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
