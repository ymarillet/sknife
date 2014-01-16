<?php
namespace Fudge\Sknife\Model\Interfaces;

/**
 * HasPermissionsInterface
 * @author Yohann Marillet
 * @since 12/12/13
 */
interface HasPermissionsInterface
{
    /**
     * Returns the object's permissions
     * @return array
     * @author Yohann Marillet
     */
    public function getPermissions();
} 