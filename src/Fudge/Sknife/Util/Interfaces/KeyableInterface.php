<?php
namespace Fudge\Sknife\Util\Interfaces;

/**
 * Keyable
 * @author Yohann Marillet
 * @since 25/09/13
 */
interface KeyableInterface
{
    /**
     * Gets an unique representation of an object (ideally a string or an int) amongst the objects of the same type
     * @return mixed
     * @author Yohann Marillet
     */
    public function getKey();
}
