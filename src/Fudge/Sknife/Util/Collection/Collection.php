<?php
namespace Fudge\Sknife\Util\Collection;

use Fudge\Sknife\Exception\BusinessException;
use Fudge\Sknife\Exception\CollectionException;
use Fudge\Sknife\Exception\KeyableException;
use Fudge\Sknife\Exception\TypeExpectedException;
use Fudge\Sknife\Util\Interfaces\KeyableInterface;
use Fudge\Sknife\Util\NullObject;
use Fudge\Sknife\Util\Objects;
use Fudge\Sknife\Util\Strings;
use Fudge\Sknife\Util\Interfaces\TaintedInterface;

/**
 * Collection
 * @author Yohann Marillet
 * @since 25/09/13
 */
abstract class Collection extends \ArrayIterator implements CollectionInterface
{
    protected $_authorize_null_object = false;
    protected $_exception_class = '\\Fudge\\Sknife\\Exception\\ClassExpectedException';

    public function add($value)
    {
        $is_array_collection = false;
        $element_type = $this->getElementType();
        if ((is_array($value) || ($value instanceof \Traversable)) && !empty($value)) {
            $first_element = reset($value);
            $is_array_collection = ($first_element instanceof $element_type);
        }

        if ($is_array_collection) {
            $method = __FUNCTION__;
            foreach ($value as $object) {
                $this->$method($object);
            }
        } else {
            $this->offsetSet($value, $value);
        }

        return $this;
    }

    public function remove($index)
    {
        $class = get_class($this);
        if (is_array($index) || $index instanceof $class) {
            $method = __FUNCTION__;
            foreach ($index as $object) {
                $this->$method($object);
            }
        } else {
            $this->offsetUnset($index);
        }

        return $this;
    }

    public function modify($data)
    {
        $class = get_class($this);
        if (is_array($data) || $data instanceof $class) {
            $method = __FUNCTION__;
            foreach ($data as $object) {
                $this->$method($object);
            }
        } elseif ($data instanceof TaintedInterface) {
            $this->_modify($data);
        } else {
            throw new BusinessException('Unsupported data format for modify');
        }

        return $this;
    }

    /**
     * Modify a single object in the collection
     *
     * @param TaintedInterface $object
     *
     * @throws BusinessException
     * @return $this
     */
    protected function _modify(TaintedInterface $object)
    {
        try {
            $obj = $this->offsetGet($object);

            if ($object === $obj) {
                throw new BusinessException("Logic error: the parameter must be a different object from the one used in the collection.");
            }

            foreach ($object->getTainted() as $key => $val) {
                $func = Strings::toSetter($key);
                $obj->$func($val);
            }
        } catch (CollectionException $e) {
            //this element is new to the collection, simply insert it into the collection (deep copy)

            $obj = Objects::deepCopy($object);
            $this->offsetSet($obj, $obj);
        }

        return $this;
    }

    /**
     * @return $this
     * @author Yohann Marillet
     */
    public function resetTainted()
    {
        foreach ($this as $e) {
            if ($e instanceof TaintedInterface) {
                $e->resetTainted();
            }
        }

        return $this; //fluent interface
    }

    /**
     * @return bool
     * @author Yohann Marillet
     */
    public function hasTainted()
    {
        $return = false;

        foreach ($this as $e) {
            if ($e instanceof TaintedInterface) {
                $return = $e->hasTainted();
                if ($return) {
                    break;
                }
            }
        }

        return $return;
    }

    /**
     * @return CollectionInterface|mixed
     * @author Yohann Marillet
     */
    public function deepCopy()
    {
        return Objects::deepCopy($this);
    }

    /**
     * @return array
     * @author Yohann Marillet
     */
    public function getKeys()
    {
        $return = array();

        foreach ($this as $object) {
            $return[] = $this->getKey($object);
        }

        return $return;
    }

    /**
     * @param $value
     *
     * @return mixed
     * @throws \Fudge\Sknife\Exception\KeyableException
     * @author Yohann Marillet
     */
    public function getKey($value)
    {
        $return = $value;

        if ($value instanceof KeyableInterface) {
            $return = $value->getKey();
            if (empty($return)) {
                throw new KeyableException('The key cannot be empty');
            }
        }

        return $return;
    }

    /**
     * @param mixed|string $index
     * @param mixed|string $newval
     *
     * @throws TypeExpectedException
     * @throws BusinessException
     * @author Yohann Marillet
     */
    public function offsetSet($index, $newval)
    {
        $element_type = $this->getElementType();
        $non_object_types = array(
            'boolean',
            'bool',
            'int',
            'integer',
            'double',
            'float',
            'string',
            'array',
        );
        $is_object = (!in_array($element_type, $non_object_types));
        $exception_message = '%s expected for $newval, got %s';
        if ($is_object) {
            if (!($newval instanceof $element_type)) {
                if (!($this->_authorize_null_object && ($newval instanceof NullObject))) {
                    $class = $this->getExceptionClass();
                    throw new $class(sprintf($exception_message, $element_type, get_class($newval)));
                }
            }
        } else {
            $type = gettype($newval);
            if ($element_type != $type) {
                throw new TypeExpectedException(sprintf($exception_message, $element_type, $type));
            }
        }
        $offset = $this->getKey($index);
        if (!is_scalar($offset)) {
            throw new BusinessException('Cannot set a non scalar value as an offset in a collection.');
        }
        parent::offsetSet($offset, $newval);
    }

    /**
     * @param mixed|string $index
     *
     * @author Yohann Marillet
     */
    public function offsetUnset($index)
    {
        $offset = $this->getKey($index);
        parent::offsetUnset($offset);
    }

    /**
     * @param mixed|string $value
     *
     * @return mixed
     * @throws CollectionException
     * @author Yohann Marillet
     */
    public function offsetGet($value)
    {
        $k = $this->getKey($value);
        if (!isset($this[$k])) {
            throw new CollectionException("This key (or Keyable object) has not been defined in the collection. \n\nContent: \n" . var_export(
                $value,
                true
            ));
        }

        return parent::offsetGet($k);
    }

    /**
     * @param mixed $value
     *
     * @author Yohann Marillet
     */
    public function append($value)
    {
        $this->add($value);
    }

    /**
     * @return string
     * @author Yohann Marillet
     */
    protected function getExceptionClass()
    {
        return $this->_exception_class;
    }
}
