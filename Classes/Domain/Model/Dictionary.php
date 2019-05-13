<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Domain\Model;

use ArrayAccess;

/**
 * Dictionary is a Document subclass that implements ArrayAccess
 */
class Dictionary extends Document implements ArrayAccess
{
    public function offsetExists($offset)
    {
        return (bool)$this->valueForKey((string)$offset);
    }

    public function offsetGet($offset)
    {
        return $this->valueForKey((string)$offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->setValueForKey((string)$offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->setValueForKey((string)$offset, null);
    }
}
