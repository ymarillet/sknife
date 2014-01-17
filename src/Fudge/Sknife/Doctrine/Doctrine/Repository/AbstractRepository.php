<?php
namespace Fudge\Sknife\ORM\Doctrine\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Fudge\Sknife\Model\Traits\IsSoftDeletable;
use Fudge\Sknife\Util\Strings;

/**
 * Common Doctrine repositories functions
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 23/09/13
 */
abstract class AbstractRepository extends EntityRepository
{
    /**
     * Adds common filter options (limit, offset, order) to a query builder
     * @param  QueryBuilder $qb
     * @param  array        $options
     * @return QueryBuilder
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    protected function addCommonFilters(QueryBuilder $qb, Array $options=array())
    {
        if (isset($options['_prefix'])) {
            $options['_prefix'] = rtrim($options['_prefix'],'.').'.';
        } else {
            $options['_prefix']='';
        }

        if (isset($options['limit']) && !empty($options['limit'])) {
            $qb->setMaxResults((int) $options['limit']);

            if (isset($options['offset'])) {
                $qb->setFirstResult((int) $options['offset']);
            }
        }

        if (isset($options['order'])) {
            foreach ($options['order'] as $field => $order) {
                if (is_int($field)) {
                    $field = $order;
                    $order = 'ASC';
                }

                $field = $options['_prefix'] . $field;
                $qb->addOrderBy($this->replaceByJoinAlias($field, $qb), $order);
            }
        }

        return $qb;
    }

    /**
     * Transform common filter options (limit, offset, order) for a native query
     * @param  array $options
     * @return array
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    protected function getNativeCommonFilters(Array $options=[])
    {
        $return = [];

        if (isset($options['limit'])) {
            $return['limit'] = (int) $options['limit'];

            if (isset($options['offset'])) {
                $return['offset'] = (int) $options['offset'];
            }
        }

        if (isset($options['order']) && !empty($options['order'])) {
            $return['order']='';
            foreach ($options['order'] as $field => $order) {
                if (is_int($field)) {
                    $field = $order;
                    $order = 'ASC';
                }

                if (!empty($return['order'])) {
                    $return['order'].=', ';
                }
                $return['order'] .= $field.' '.$order;
            }
        }

        return $return;
    }

    /**
     * Add search filters to a query builder
     *
     * @param  QueryBuilder $qb
     * @param  array        $options filters to apply - only following keys are supported: search, globalSearch, globalSearchColumns, _prefix
     * @throws \Exception
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    protected function addSearchFilters($qb, $options=[])
    {
        if (isset($options['_prefix'])) {
            $options['_prefix'] = rtrim($options['_prefix'],'.').'.';
        } else {
            $options['_prefix']='';
        }

        $i = 0;
        if (isset($options['search']) && !empty($options['search'])) {
            foreach ($options['search'] as $k => $e) {
                $qbt = '';

                if (!is_array($e) || isset($e['value'])) {
                    $e = array($e);
                }

                foreach ($e as $v) {
                    if (!isset($v['value'])) {
                        if (is_scalar($v)) {
                            $v = [
                                'value' => $v,
                            ];
                        } else {
                            throw new \Exception(sprintf(
                                '$v is expected to be scalar. Given: %s',
                                Strings::getClassOrType($v)
                            ));
                        }
                    }

                    if (!isset($v['type'])) {
                        $v['type'] = 'scalar';
                    }

                    $condition = '';
                    if ($v['type'] == 'scalar') {
                        $condition = $qb->expr()->like(
                            $this->replaceByJoinAlias($options['_prefix'] . $k, $qb),
                                ':param' . ++$i
                        );
                        $qb->setParameter(':param' . $i, "%" . $v['value'] . "%");
                    } elseif ($v['type'] == 'array') {
                        $condition = $qb->expr()->in(
                            $this->replaceByJoinAlias($options['_prefix'] . $k, $qb),
                                ':param' . ++$i
                        );
                        $qb->setParameter('":param"'.$i, $v['value']);
                    } elseif ($v['type'] == 'callback') {
                        $condition = $this->$k($qb, $v['value'], $options['_prefix']);
                    }

                    $qbt .= (!empty($qbt) ? ' AND ' : '') .
                            '(' .
                            $condition .
                            ')';
                }

                $qb->andWhere($qbt);
            }
        }

        if (isset($options['globalSearch'])
                && !empty($options['globalSearch'])
                && isset($options['globalSearchColumns'])
                && !empty($options['globalSearchColumns'])
        ) {
            $qbt = '';
            foreach ($options['globalSearchColumns'] as $e) {
                $qbt .= (!empty($qbt) ? ' OR ' : '') . '(' . $qb->expr()->like(
                            $this->replaceByJoinAlias($options['_prefix'] . $e, $qb),
                            "'%{$options['globalSearch']}%'"
                        ) . ')';
            }
            $qb->andWhere($qbt);
        }
    }

    /**
     * Format search filters for a native query
     *
     * @param array $options filters to apply - only following keys are supported: search, globalSearch, globalSearchColumns
     * @retuen array
     * @throws \Exception
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    protected function getNativeSearchFilters($options=[])
    {
        $return['where'] = '';
        $i = 0;
        if (isset($options['search']) && !empty($options['search'])) {
            foreach ($options['search'] as $k => $e) {
                $qbt = '';

                if (!is_array($e) || isset($e['value'])) {
                    $e = array($e);
                }

                foreach ($e as $v) {
                    if (!isset($v['value'])) {
                        if (is_scalar($v)) {
                            $v = [
                                'value' => $v,
                            ];
                        } else {
                            throw new \Exception(sprintf(
                                '$v is expected to be scalar. Given: %s',
                                Strings::getClassOrType($v)
                            ));
                        }
                    }

                    if (!isset($v['type'])) {
                        $v['type'] = 'scalar';
                    }

                    $condition = '';
                    if ($v['type'] == 'scalar') {
                        $condition = $k . " LIKE ".$this->getEntityManager()->getConnection()->quote('%'.str_replace('%','',$v['value']).'%');
                    } elseif ($v['type'] == 'array') {
                        array_walk($v['value'], [$this->getEntityManager()->getConnection(), 'quote']);
                        $values = implode(',',$v['value']);
                        $condition = $k . ' IN ('.$values.')';
                    } elseif ($v['type'] == 'callback') {
                        $condition = $this->$k($v['value']);
                    }

                    $qbt .= (!empty($qbt)?' AND ':'') . '('.$condition.')';
                }

                $return['where'] .= 'AND ('.$qbt.')';
            }
        }

        if (isset($options['globalSearch'])
                && !empty($options['globalSearch'])
                && isset($options['globalSearchColumns'])
                && !empty($options['globalSearchColumns'])
        ) {
            $qbt = '';
            $globalSearchValue = $this->getEntityManager()->getConnection()->quote('%'.str_replace('%','',$options['globalSearch']).'%');
            foreach ($options['globalSearchColumns'] as $e) {
                $qbt .= (!empty($qbt) ? ' OR ' : '') . "($e LIKE $globalSearchValue)";
            }
            $return['where'] .= 'AND ('.$qbt.')';
        }

        return $return;
    }

    /**
     * Replaces a column alias (multiple level) by its joined representation alias
     * Ex. A query builder with: FROM news_version nv JOIN news n
     * Replaces "nv.news.XXX" by "n.XXX"
     *
     * @param $alias
     * @param  QueryBuilder $qb
     * @return string
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    protected function replaceByJoinAlias($alias, QueryBuilder $qb)
    {
        $joins = $qb->getDQLPart('join');
        $return = $alias;
        foreach ($joins as $j) {
            /**
             * @var $e Expr\Join
             */
            foreach ($j as $e) {
                $return = str_replace($e->getJoin().'.', $e->getAlias().'.', $return);
            }
        }

        return $return;
    }

    /**
     * Slices a doctrine collection from an array of filters
     * @param  Collection                $storeNewsCollection
     * @param  array                     $filters
     * @throws \InvalidArgumentException
     * @return array
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function slice(Collection $collection, array $filters)
    {
        if (!isset($filters['offset'])) {
            throw new \InvalidArgumentException('Filter key "offset" must be specified');
        }

        if (!isset($filters['limit'])) {
            $filters['limit'] = null;
        } else {
            $filters['limit'] = (int) $filters['limit'];
        }

        $return = $collection->slice((int) $filters['offset'], $filters['limit']);

        return $return;
    }

    /**
     * Returns the value of a DQL field in an entity
     * @param $entity
     * @param string $field
     * @param string $separator
     *
     * @return mixed
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    protected function getValueFromDQLField($entity, $field, $separator='.')
    {
        $fields = explode($separator,$field);
        $o = $entity;
        foreach ($fields as $f) {
            $getter = Strings::toGetter($f);
            $o =  $o->$getter();
        }

        return $o;
    }

    /**
     * Filters a doctrine collection from an array of filters
     * @param  Collection $collection
     * @param  array      $filters
     * @return Collection
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function filter(Collection $collection, array $filters)
    {
        $globalSearch = !empty($filters['globalSearch'])?array_flip($filters['globalSearchColumns']):[];
        $toSearch = array_merge($filters['search']+$globalSearch);

        $return = $collection->filter(function ($e) use ($filters, $globalSearch, $toSearch) {
            $return = true;
            $globalFilterResult = empty($globalSearch);
            // iterate through the attributes
            foreach ($toSearch as $col => $weDontCareAboutThisValue) {
                $o = $this->getValueFromDQLField($e, $col);

                if (is_scalar($o)) {
                    $o = array($o);
                }

                if (empty($o) && isset($filters['search'][$col]) && !empty($filters['search'][$col])) {
                    $return = false;
                }

                // iterate through the values of an attribute
                foreach ($o as $val) {
                    if (!$globalFilterResult && isset($globalSearch[$col])) {
                        //if the attribute is eligible for the global filter
                        $globalFilterResult = (false!==stristr($val, $filters['globalSearch']));
                    }

                    $columnFiltersResults = true;
                    if (isset($filters['search'][$col]) && !empty($filters['search'][$col])) {
                        //if the attribute is eligible for a column filter
                        $columnFiltersResults = false;

                        // iterate through the values of the filter ("or" conditioning)
                        foreach ($filters['search'][$col] as $searchString) {
                            $columnFiltersResults = $columnFiltersResults || (false!==stristr($val, $searchString));
                            if ($columnFiltersResults) {
                                break;
                            }
                        }
                    }

                    $return = $return && $columnFiltersResults;

                    if (!$return) {
                        break;
                    }
                }

                if (!$return) {
                    break;
                }
            }

            $return = $return && $globalFilterResult;

            return $return;
        });

        return $return;
    }

    /**
     * Sorts a doctrine collection from an array of filters
     * @param  Collection $collection
     * @param  array      $filters
     * @return Collection
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     * @deprecated this function is very slow on large sets of data
     */
    public function sort(Collection $collection, array $filters)
    {
        if (isset($filters['sort'])) {
            /** @var \ArrayIterator $it */
            $it = $collection->getIterator();
            $that = $this;
            $it->uasort(function ($first, $second) use ($filters, $that) {
                $return = 0;
                if ($first !== $second) {
                    $sortFilters = $filters['sort'];
                    $cur = reset($sortFilters);
                    while ($return==0 && false !== $cur) {
                        $k = key($sortFilters);
                        $v1 = $that->getValueFromDQLField($first, $k);
                        $v2 = $that->getValueFromDQLField($second, $k);
                        if ($v1 != $v2) {
                            $return = $v1 > $v2 ? 1 : -1;
                            //var_dump($v1.', '.$v2.' : '.$return);

                            if ($cur=='DESC') {
                                $return = $return * -1;
                            }
                        }
                        $cur=next($sortFilters);
                    }
                }

                return $return;
            });
        }

        return $collection;
    }

    /**
     * Reindexes a collection with a new key (does not affect the input collection; returns a new collection)
     * @param Collection $collection
     * @param $index
     * @return ArrayCollection
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function reIndex(Collection $collection, $index)
    {
        $return = new ArrayCollection();

        $attributesList = explode('.',$index);
        foreach ($attributesList as $k=>$v) {
            $attributesList[$k] = Strings::toGetter($v);
        }
        foreach ($collection as $e) {
            $key = $e;
            foreach ($attributesList as $getter) {
                $key = $key->$getter();
            }
            $return[$key] = $e;
        }

        return $return;
    }

    /**
     * Deletes an entity or a list of entities
     * @param mixed $list list of entities / entity IDs
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function delete($list)
    {
        if (!is_array($list) && !($list instanceof \Traversable)) {
            $list = [$list];
        }

        $className = $this->getClassName();
        foreach ($list as $entity) {
            if (!($entity instanceof $className)) {
                $entity = $this->retrieveEntityForDelete($className, $entity);
            }

            $this->preRemove($entity);
            $this->getEntityManager()->remove($entity);
        }
        $this->getEntityManager()->flush();
    }

    /**
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function preRemove($entity)
    {
        //implement in subclasses if needed
    }

    /**
     * Method which is used by $this->delete() to retrieve an entity
     * (usually modified in subclasses to get a specific partial reference (performance) or a complete entity in the
     * case you need to add complexity)
     *
     * @param $className
     * @param $identifier
     *
     * @return string
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    protected function retrieveEntityForDelete($className, $identifier)
    {
        return $this->getEntityManager()->getPartialReference($className, $identifier);
    }

    /**
     * Soft deletes a record (implementing IsSoftDeletable)
     * @param $list
     * @return array ID list of actual soft deleted records
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function softDelete($list)
    {
        if (!is_array($list) && !($list instanceof \Traversable)) {
            $list = [$list];
        }

        $className = $this->getClassName();
        $idName = $this->getClassMetadata()->getIdentifier();

        //does not work for compound PKs
        $idName = reset($idName);
        $getter = Strings::toGetter($idName);

        $notDeleted = [];
        $resolveAlreadyDeleted = false;
        if (reset($list) instanceof $className) {
            $entities = $list;
        } else {
            $entities = $this->findByIdentifiersArray($list);
            $resolveAlreadyDeleted = true;
        }

        $return = [];
        foreach ($entities as $entity) {
            $id = $entity->$getter();
            if ($resolveAlreadyDeleted) {
                $notDeleted[] = $id;
            }
            /** @var IsSoftDeletable $entity */
            if ($entity->isSoftDeletable()) {
                $return[] = $id;
                $entity->setIsDeleted(true);
                $this->getEntityManager()->persist($entity);
            }
        }

        if (!empty($return)) {
            $this->getEntityManager()->flush();
        }

        if ($resolveAlreadyDeleted) {
            $diff = array_diff($list, $notDeleted);
            $return = $return + $diff;
        }

        return $return;
    }

    /**
     * @param  array $list
     * @return array
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function findByIdentifiersArray(Array $list)
    {
        //does not work for compound PKs
        $idName = $this->getClassMetadata()->getIdentifier();
        $idName = reset($idName);

        $qb = $this->createQueryBuilder('e');
        $in = $qb->expr()->in('e.'.$idName, array_values($list));
        $qb->where($in);

        $return = $qb->getQuery()->getResult();

        return $return;
    }
}
