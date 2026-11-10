<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Persistence;

use Cundd\DocumentStorage\Exception\InvalidDocumentException;
use JsonException;

use function json_decode;
use function json_encode;
use function strtolower;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

final class JsonSerializer
{
    /**
     * @param array<mixed,mixed> $data
     */
    public function serialize(?array $data): string
    {
        try {
            return json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new InvalidDocumentException(
                'Could not serialize data',
                1790171909
            );
        }
    }

    /**
     * @return mixed|null
     */
    public function deserialize(?string $data): mixed
    {
        if (!$data || 'null' === strtolower($data)) {
            return null;
        }

        try {
            return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidDocumentException(
                'Invalid JSON data: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
