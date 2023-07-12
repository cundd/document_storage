<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Command\Output;

use JsonSerializable;
use RuntimeException;
use Throwable;

class NotFoundException extends RuntimeException implements JsonSerializable
{
    private static NotFoundException $sharedInstance;

    /**
     * Return the shared instance
     *
     * @return static
     */
    public static function instance(): self
    {
        if (!static::$sharedInstance) {
            static::$sharedInstance = new static();
        }

        return static::$sharedInstance;
    }

    /**
     * Make the constructor private
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    private function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function getSymbol(): string
    {
        return 'NotFoundException:LkZ1IGz8671-1MqNg4sMgIptdsr0afk1YcPVNfTNsDs=';
    }

    public function jsonSerialize(): string
    {
        return static::getSymbol();
    }
}
