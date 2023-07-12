<?php

declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Model;

use ArrayAccess;

/**
 * Dictionary is a Document subclass that implements ArrayAccess
 */
class Dictionary extends Document implements ArrayAccess
{
    public function offsetExists($offset): bool
    {
        return (bool)$this->valueForKey((string)$offset);
    }

    public function offsetGet($offset): mixed
    {
        return $this->valueForKey((string)$offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->setValueForKey((string)$offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->setValueForKey((string)$offset, null);
    }
}
