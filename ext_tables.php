<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('document_storage', 'Configuration/TypoScript', 'Document Storage');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_documentstorage_domain_model_document', 'EXT:document_storage/Resources/Private/Language/locallang_csh_tx_documentstorage_domain_model_document.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_documentstorage_domain_model_document');

    }
);
