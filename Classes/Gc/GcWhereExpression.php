<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Gc;

use InvalidArgumentException;
use function count;
use function substr_count;

class GcWhereExpression
{
    /**
     * @var string
     */
    private $sqlString;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * GcWhereExpression constructor.
     *
     * @param string $sqlString
     * @param array  $parameters
     */
    public function __construct(string $sqlString, array $parameters)
    {
        if (count($parameters) !== substr_count($sqlString, '?')) {
            throw new InvalidArgumentException('Number of parameters does not match the number of SQL placeholders');
        }
        $this->sqlString = $sqlString;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getSqlString(): string
    {
        return $this->sqlString;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function __toString()
    {
        return $this->getSqlString();
    }
}
