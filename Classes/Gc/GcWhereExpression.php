<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Gc;

use InvalidArgumentException;

use function count;
use function substr_count;

readonly class GcWhereExpression
{
    /**
     * @param array<string,string|int> $parameters
     */
    public function __construct(
        public string $sqlString,
        public array $parameters,
    ) {
        if (count($parameters) !== substr_count($sqlString, '?')) {
            throw new InvalidArgumentException('Number of parameters does not match the number of SQL placeholders');
        }
    }
}
