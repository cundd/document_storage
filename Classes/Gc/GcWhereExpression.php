<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Gc;

use InvalidArgumentException;

use function count;
use function substr_count;

class GcWhereExpression
{
    private string $sqlString;

    private array $parameters = [];

    public function __construct(string $sqlString, array $parameters)
    {
        if (count($parameters) !== substr_count($sqlString, '?')) {
            throw new InvalidArgumentException('Number of parameters does not match the number of SQL placeholders');
        }
        $this->sqlString = $sqlString;
        $this->parameters = $parameters;
    }

    public function getSqlString(): string
    {
        return $this->sqlString;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function __toString()
    {
        return $this->getSqlString();
    }
}
