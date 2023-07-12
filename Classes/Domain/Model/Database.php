<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Model;

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
     * @var \DateTimeImmutable
     */
    private $creationTime;

    /**
     * Database constructor
     *
     * @param string             $name
     * @param \DateTimeImmutable $creationTime
     */
    public function __construct(
        string $name,
        ?\DateTimeImmutable $creationTime
    ) {
        $this->name = $name;
        $this->creationTime = $creationTime;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreationTime(): ?\DateTimeImmutable
    {
        return $this->creationTime;
    }
}
