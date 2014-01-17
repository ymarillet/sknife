<?php
namespace Fudge\Sknife\Model\Interfaces;

/**
 * HasPermissionsInterface
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 12/12/13
 */
interface HasPermissionsInterface
{
    /**
     * Returns the object's permissions
     * @return array
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function getPermissions();
} 