<?php
namespace Fudge\Sknife\Util\Collection;

/**
 * Description
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 25/09/13
 */
interface CollectionInterface extends \ArrayAccess, \SeekableIterator, \Countable, \Serializable
{
    /**
     * Adds an element (or more) to the collection
     * @param mixed $value
     * @return $this
     */
    public function add($value);

    /**
     * Removes an element (or more) from the collection
     * @param mixed $index
     * @return $this
     */
    public function remove($index);

    /**
     * Modify the elements of the collection with the given data (other collection, array, single element)
     * @param mixed $data
     * @return $this
     */
    public function modify($data);

    /**
     * Makes a deep copy of the collection
     * @return CollectionInterface
     */
    public function deepCopy();

    /**
     * If $value implements the Keyable interface, will return its ->getKey() value, otherwhise return $value
     * @return mixed
     */
    public function getKey($value);

    /**
     * Gets all the keys contained in the collection
     * @return array
     */
    public function getKeys();

    /**
     * Gets the element type of the collection
     * @return string
     */
    public function getElementType();

    /**
     * If the elements implements the Tainted interface, this will action the resetTainted function for all elements in the collection
     * @return $this
     */
    public function resetTainted();

    /**
     * If the elements implements the Tainted interface, this action will test if there is at least one modified element in the collection
     * @return bool
     */
    public function hasTainted();
}
