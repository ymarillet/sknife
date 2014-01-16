<?php
namespace Fudge\Sknife\Model\Traits;

use Fudge\Sknife\Exception\BusinessException;
use Fudge\Sknife\Util\Collection\Collection;

/**
 * InstantiableConstantContainer
 * @author Yohann Marillet
 * @since 25/09/13
 *
 * @method bool test() test(mixed $item)
 * @method mixed get() get(mixed $item)
 * @method array getAll() getAll()
 */
trait InstantiableConstantContainer
{
    protected $item;

    public function __construct($item)
    {
        $this->setItem($item);
    }

    public function setItem($item)
    {
        static::test($item);
        $this->item = $item;

        return $this; //fluent interface
    }

    public function getItem()
    {
        return $this->item;
    }

    public function __toString()
    {
        return static::get($this->item);
    }

    /**
     * @throws BusinessException
     * @return string
     * @author Yohann Marillet
     */
    public static function getCollectionClass()
    {
        throw new BusinessException('You need to provide a collection class in this function');
    }

    public static function getCollection()
    {
        $all = static::getAll();
        $collectionClass = static::getCollectionClass();

        /**
         * @var $return Collection
         */
        $return = new $collectionClass;
        foreach ($all as $k=>$e) {
            $return->append(new static($k));
        }

        return $return;
    }
}
