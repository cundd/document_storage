<?php
return [
    'ctrl'      => [
        'title'         => 'LLL:EXT:document_storage/Resources/Private/Language/locallang_db.xlf:tx_documentstorage_domain_model_document',
        'label'         => 'id',
        'tstamp'        => 'tstamp',
        'crdate'        => 'crdate',
        'cruser_id'     => 'cruser_id',
        'delete'        => 'deleted',
        'enablecolumns' => [
        ],
        'searchFields'  => 'id,db,data_protected',
        'iconfile'      => 'EXT:document_storage/Resources/Public/Icons/tx_documentstorage_domain_model_document.gif',
    ],
    'interface' => [
        'showRecordFieldList' => 'id, db, data_protected',
    ],
    'types'     => [
        '1' => ['showitem' => 'id, db, data_protected'],
    ],
    'columns'   => [
        'id'             => [
            'exclude' => false,
            'label'   => 'LLL:EXT:document_storage/Resources/Private/Language/locallang_db.xlf:tx_documentstorage_domain_model_document.id',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
            ],
        ],
        'db'             => [
            'exclude' => false,
            'label'   => 'LLL:EXT:document_storage/Resources/Private/Language/locallang_db.xlf:tx_documentstorage_domain_model_document.db',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
            ],
        ],
        'data_protected' => [
            'exclude' => false,
            'label'   => 'LLL:EXT:document_storage/Resources/Private/Language/locallang_db.xlf:tx_documentstorage_domain_model_document.data_protected',
            'config'  => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim,required',
            ],
        ],
        'deleted'        => [
            'exclude' => true,
            'label'   => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.deleted',
            'config'  => [
                'type'       => 'check',
                'renderType' => 'checkboxToggle',
                'items'      => [
                    [
                        0                    => '',
                        1                    => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
    ],
];
