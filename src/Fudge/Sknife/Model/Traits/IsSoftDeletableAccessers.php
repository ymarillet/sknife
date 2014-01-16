<?php
namespace Fudge\Sknife\Model\Traits;

/**
 * Description
 * @author Yohann Marillet
 * @since 28/10/13
 */
trait IsSoftDeletableAccessers
{
    /**
     * @param bool $isDeletable
     *
     * @return $this
     * @author Yohann Marillet
     */
    public function setIsDeleted($isDeletable)
    {
        $this->isDeleted = true==$isDeletable;

        return $this; //fluent interface
    }

    /**
     * @return bool
     * @author Yohann Marillet
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @return bool
     * @author Yohann Marillet
     */
    public function isSoftDeletable()
    {
        return true;
    }
}
