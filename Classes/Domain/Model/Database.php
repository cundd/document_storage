<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Model;

use DateTimeImmutable;

/**
 * Class Database
 *
 * Information about a database as specified in Documents
 */
class Database
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var DateTimeImmutable
     */
    private $creationTime;

    /**
     * Database constructor
     */
    public function __construct(
        string $name,
        ?DateTimeImmutable $creationTime,
    ) {
        $this->name = $name;
        $this->creationTime = $creationTime;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCreationTime(): ?DateTimeImmutable
    {
        return $this->creationTime;
    }
}
