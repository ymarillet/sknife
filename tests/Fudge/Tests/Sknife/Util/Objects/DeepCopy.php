<?php
namespace Fudge\Tests\Sknife\Util\Objects;

/**
 * DeepCopy
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 17/01/14
 */
class DeepCopy
{
    protected $injection;

    /**
     * @param bool $inject
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function __construct($inject=true) {
        if($inject) {
            $this->injection = new self(false);
        }
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function getInjection()
    {
        return $this->injection;
    }

} 