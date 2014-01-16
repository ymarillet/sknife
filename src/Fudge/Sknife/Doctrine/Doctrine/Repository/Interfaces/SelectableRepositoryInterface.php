<?php
namespace Fudge\Sknife\ORM\Doctrine\Repository\Interfaces;

/**
 * SelectableRepositoryInterface
 * @author Yohann Marillet
 * @since 24/10/13
 */
interface SelectableRepositoryInterface
{
    /**
     * Given a list of table identifiers, will check for any removed IDs and return a new IDs list
     * @param $identifiersList
     *
     * @return array
     * @author Yohann Marillet
     */
    public function refreshSelected(Array $identifiersList);
}
