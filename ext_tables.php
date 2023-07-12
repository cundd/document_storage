<?php
defined('TYPO3') or die();

call_user_func(
    function()
    {


        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_documentstorage_domain_model_document', 'EXT:document_storage/Resources/Private/Language/locallang_csh_tx_documentstorage_domain_model_document.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_documentstorage_domain_model_document');

    }
);
