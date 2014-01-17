<?php
namespace Fudge\Sknife\Model\Traits;

/**
 * Description
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 28/10/13
 */
trait IsSoftDeletableProperty
{
    /**
     * @var bool
     * @ORM\Column(name="is_deleted", type="boolean", nullable=false)
     */
    protected $isDeleted;
}
