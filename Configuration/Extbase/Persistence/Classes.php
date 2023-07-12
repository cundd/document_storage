<?php

declare(strict_types=1);

return [
    \Cundd\DocumentStorage\Domain\Model\Document::class => [
        'properties' => [
            'modificationTime' => [
                'fieldName' => 'tstamp',
            ],
            'creationTime'     => [
                'fieldName' => 'crdate',
            ],
        ],
    ],
];
