<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {

        if (TYPO3_MODE === 'BE') {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'Pixelant.Importify',
                'tools', // Make module a submodule of 'tools'
                'importdata', // Submodule key
                '', // Position
                [
                    'Import' => 'list, show, new, create, edit, update, delete, upload',
                ],
                [
                    'access' => 'user,group',
                    'icon'   => 'EXT:importify/Resources/Public/Icons/user_mod_importdata.svg',
                    'labels' => 'LLL:EXT:importify/Resources/Private/Language/locallang_importdata.xlf',
                ]
            );
        }

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            'importify',
            'Configuration/TypoScript',
            'Importify'
        );
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
            'tx_importify_domain_model_import',
            'EXT:importify/Resources/Private/Language/locallang_csh_tx_importify_domain_model_import.xlf'
        );
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
            'tx_importify_domain_model_import'
        );
    }
);

## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line 
## is overwritten with the defaults of the extension builder
call_user_func(
    function () {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(
            'Pixelant\\Importify\\Property\\TypeConverter\\UploadedFileReferenceConverter'
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(
            'Pixelant\\Importify\\Property\\TypeConverter\\ObjectStorageConverter'
        );
    }
);
