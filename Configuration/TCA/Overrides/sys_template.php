<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

(static function () {
    ExtensionManagementUtility::addStaticFile('document_storage', 'Configuration/TypoScript', 'Document Storage');
})();
