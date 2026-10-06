<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Model;

use DateTimeImmutable;

/**
 * Information about a database as specified in Documents
 */
final readonly class Database
{
    public function __construct(
        public string $name,
        public ?DateTimeImmutable $creationTime,
    ) {
    }

    /**
     * @deprecated use $name directly
     */
    public function getName(): string
    {
        trigger_error(
            __METHOD__ . ' is deprecated use property instead',
            E_USER_DEPRECATED
        );

        return $this->name;
    }

    /**
     * @deprecated use $creationTime directly
     */
    public function getCreationTime(): ?DateTimeImmutable
    {
        trigger_error(
            __METHOD__ . ' is deprecated use property instead',
            E_USER_DEPRECATED
        );

        return $this->creationTime;
    }
}
