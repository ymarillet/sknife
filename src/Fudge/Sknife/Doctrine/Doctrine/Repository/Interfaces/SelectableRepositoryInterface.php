<?php
namespace Fudge\Sknife\ORM\Doctrine\Repository\Interfaces;

/**
 * SelectableRepositoryInterface
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 24/10/13
 */
interface SelectableRepositoryInterface
{
    /**
     * Given a list of table identifiers, will check for any removed IDs and return a new IDs list
     * @param $identifiersList
     *
     * @return array
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function refreshSelected(Array $identifiersList);
}
