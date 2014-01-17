<?php
namespace Fudge\Sknife\Util\Interfaces;

/**
 * Keyable
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 25/09/13
 */
interface KeyableInterface
{
    /**
     * Gets an unique representation of an object (ideally a string or an int) amongst the objects of the same type
     * @return mixed
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function getKey();
}
