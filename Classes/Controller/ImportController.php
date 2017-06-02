<?php
namespace Pixelant\Importify\Controller;

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
    public function showAction(\Vendor\Importtypo3\Domain\Model\Import $import)
    {
        $this->view->assign('import', $import);
    }
}
