<?php
namespace Pixelant\Importify\Controller;

use Pixelant\Importify\Property\TypeConverter\UploadedFileReferenceConverter;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
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
        $this->setTypeConverterConfigurationForImageUpload('newImport');
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
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction()
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
            $this->addFlashMessage(
                'File was missing!',
                'Error',
                $severity = \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR,
                $storeInSession = TRUE
            );
        }
        else {
            $this->importRepository->add($newImport);
            $this->addFlashMessage('Your new Import was created!');
        }
        $this->redirect('list');
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
     *
     */
    protected function setTypeConverterConfigurationForImageUpload($argumentName)
    {
        $uploadConfiguration = [
            UploadedFileReferenceConverter::CONFIGURATION_ALLOWED_FILE_EXTENSIONS => 'csv,txt',
            UploadedFileReferenceConverter::CONFIGURATION_UPLOAD_FOLDER => '1:/user_upload/',
        ];
        $newImportConfiguration = $this->arguments[$argumentName]->getPropertyMappingConfiguration();
        $newImportConfiguration->forProperty('file')
            ->setTypeConverterOptions(
                'Pixelant\\Importify\\Property\\TypeConverter\\UploadedFileReferenceConverter',
                $uploadConfiguration
            );
    }
}
