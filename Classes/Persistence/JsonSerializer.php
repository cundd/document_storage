<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Persistence;

use Cundd\DocumentStorage\Exception\InvalidDocumentException;

use function json_decode;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function strtolower;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class JsonSerializer
{
    public function serialize(?array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return mixed|null
     */
    public function deserialize(?string $data)
    {
        if (!$data || 'null' === strtolower($data)) {
            return null;
        }

        $deserialized = json_decode($data, true);
        if (null === $deserialized) {
            throw new InvalidDocumentException(
                'Invalid JSON data: ' . json_last_error_msg(),
                json_last_error()
            );
        }

        return $deserialized;
    }
}
