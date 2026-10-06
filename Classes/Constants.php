<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage;

interface Constants
{
    /**
     * Name of the property that holds the data
     */
    public const DATA_PROPERTY_NAME = 'dataProtected';

    public const DOCUMENTS_TABLE_NAME = 'tx_documentstorage_domain_model_document';

    public const DOCUMENTS_FIELD_MAPPING = [
        'documentIdentifier' => [
            'fieldName' => 'id',
        ],
        'databaseIdentifier' => [
            'fieldName' => 'db',
        ],
        'modificationTime' => [
            'fieldName' => 'tstamp',
        ],
        'modificationDateTime' => [
            'fieldName' => 'tstamp',
        ],
        'creationTime' => [
            'fieldName' => 'crdate',
        ],
        'creationDateTime' => [
            'fieldName' => 'crdate',
        ],
    ];
}
