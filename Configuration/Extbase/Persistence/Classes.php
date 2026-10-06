<?php

declare(strict_types=1);

use Cundd\DocumentStorage\Constants;
use Cundd\DocumentStorage\Domain\Model\Document;

return [
    Document::class => [
        'properties' => Constants::DOCUMENTS_FIELD_MAPPING,
    ],
];
