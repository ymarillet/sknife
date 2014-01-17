<?php
namespace Fudge\Sknife\Util;

/**
 * NullObject
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 25/09/13
 */
class NullObject implements \ArrayAccess, \Iterator, \Countable
{

    public function offsetExists($offset)
    {
        return false;
    }

    public function offsetGet($offset)
    {
        return new static();
    }

    public function offsetSet($offset, $value)
    {

    }

    public function offsetUnset($offset)
    {

    }

    public function __call($method, $args)
    {
        return new static();
    }

    public function __set($property, $value)
    {
        return new static();
    }

    public function __get($property)
    {
        return new static();
    }

    public function current()
    {
        return new static();
    }

    public function key()
    {
        return 0;
    }

    public function next()
    {
        return new static();
    }

    public function rewind()
    {

    }

    public function valid()
    {
        return false;
    }

    public function __isset($property)
    {
        return false;
    }

    public static function __callStatic($method, $args)
    {
        return new static();
    }

    public function count()
    {
        return 0;
    }

    public function __toString()
    {
        return '';
    }
}
