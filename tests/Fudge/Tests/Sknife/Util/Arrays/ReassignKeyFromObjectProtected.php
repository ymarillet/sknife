<?php
namespace Fudge\Tests\Sknife\Util\Arrays;

/**
 * ReassignKeyFromObjectProtected
 *
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 17/01/14
 */
class ReassignKeyFromObjectProtected
{
    protected $fruit;
    protected $car;

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function setCar($car)
    {
        $this->car = $car;
        return $this; //fluent interface
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function setFruit($fruit)
    {
        $this->fruit = $fruit;
        return $this; //fluent interface
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function getFruit()
    {
        return $this->fruit;
    }


}