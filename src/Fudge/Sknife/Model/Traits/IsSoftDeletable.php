<?php
namespace Fudge\Sknife\Model\Traits;

/**
 * IsSoftDeletable
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 28/10/13
 */
trait IsSoftDeletable
{
    use IsSoftDeletableProperty;
    use IsSoftDeletableAccessers;
}
