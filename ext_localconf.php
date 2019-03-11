<?php
call_user_func(
    function () {

        if (TYPO3_MODE === 'BE') {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Cundd\\Rest\\Command\\RestCommandController';
        }
    }
);
