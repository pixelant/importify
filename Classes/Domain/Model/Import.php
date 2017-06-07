<?php
namespace Pixelant\Importify\Domain\Model;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

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
 * Import
 */
class Import extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * file
     *
     * @var  \TYPO3\CMS\Extbase\Domain\Model\FileReference
     * @cascade remove
     */
    protected $file;

    /**
     * filename
     *
     * @var string
     */
    protected $filename = '';

    /**
     * Returns the file
     *
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference file
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Sets the file
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $file
     * @return void
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Returns the filename
     *
     * @return string filename
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Sets the filename
     *
     * @param string $filename
     * @return void
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }
}
