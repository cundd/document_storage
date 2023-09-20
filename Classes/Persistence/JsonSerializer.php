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
     * @param string|null $data
     * @return mixed|null
     */
    public function deserialize(?string $data)
    {
        if (!$data || strtolower($data) === 'null') {
            return null;
        }

        $deserialized = json_decode($data, true);
        if ($deserialized === null) {
            throw new InvalidDocumentException(
                'Invalid JSON data: ' . json_last_error_msg(),
                json_last_error()
            );
        }

        return $deserialized;
    }
}
