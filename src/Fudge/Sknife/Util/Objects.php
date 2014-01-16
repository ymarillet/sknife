<?php
namespace Fudge\Sknife\Util;

/**
 * Objects related functions
 * @author Yohann Marillet
 * @since 25/09/13
 */
class Objects
{
    /**
     * Make a deep copy of an object, because sometimes __clone() is not enough
     *
     * @param  Object $c
     * @return mixed
     */
    public static function deepCopy($c)
    {
        return unserialize(serialize($c));
    }
}
