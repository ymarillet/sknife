<?php
namespace Fudge\Sknife\ORM\Doctrine\Repository\Traits;

use Doctrine\ORM\QueryBuilder;

/**
 * HasTemporaryCreation
 * @author Yohann Marillet
 * @since 26/10/13
 * @method QueryBuilder createQueryBuilder()
 */
trait HasTemporaryEntityCreation
{
    /**
     * @author Yohann Marillet
     */
    public function cleanNotCreated()
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('x');
        $property = $this->getDatetimeAutoDeletePropertyName();
        $qb->delete()
            ->where('x.'.$property.' IS NOT NULL')
            ->andWhere('x.'.$property.' < :date')
            ->setParameter('date', new \DateTime())
        ;
        $qb->getQuery()->execute();
    }

    /**
     * @return string
     * @author Yohann Marillet
     */
    protected function getDatetimeAutoDeletePropertyName()
    {
        return 'datetimeAutoDelete';
    }
}
