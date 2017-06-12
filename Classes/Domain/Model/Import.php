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
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     * @cascade remove
     * @validate NotEmpty
     */
    protected $file = null;

    /**
     * delimeter
     *
     * @var string
     * @validate NotEmpty
     */
    protected $delimeter = '';

    /**
     * enclosure
     *
     * @var string
     * @validate NotEmpty
     */
    protected $enclosure = '';

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
     * Returns the delimeter
     *
     * @return string $delimeter
     */
    public function getDelimeter()
    {
        return $this->delimeter;
    }

    /**
     * Sets the delimeter
     *
     * @param string $delimeter
     * @return void
     */
    public function setDelimeter($delimeter)
    {
        $this->delimeter = $delimeter;
    }

    /**
     * Returns the enclosure
     *
     * @return string $enclosure
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * Sets the enclosure
     *
     * @param string $enclosure
     * @return void
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;
    }
}
