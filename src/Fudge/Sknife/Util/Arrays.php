<?php
namespace Fudge\Sknife\Util;

use Fudge\Sknife\Exception\BusinessException;
use Fudge\Sknife\Exception\ForeachableException;
use Fudge\Sknife\Util\Interfaces\KeyableInterface;

/**
 * Useful functions for arrays
 *
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 27/09/13
 */
class Arrays
{
    /**
     * Assuming the foreachable haystack contains KeyableInterface elements
     * this function will return an element of this array by searching on its KeyableInterface features
     * If not found, will refurn false
     *
     * @param  mixed $needle
     * @param  array|\Traversable $haystack
     * @param  int|string &$matched_key will store the matched key in this variable if given
     *
     * @throws ForeachableException
     *
     * @return mixed
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function getKeyableFromArray($needle, $haystack, &$matched_key = null)
    {
        self::validateForeachable($haystack);

        $return = false;
        foreach ($haystack as $k => $e) {
            if ($e instanceof KeyableInterface) {
                if ($needle == $e->getKey()) {
                    $matched_key = $k;
                    $return = $e;
                    break;
                }
            }
        }

        return $return;
    }

    /**
     * Flattens a 2-dimensional array
     *
     * @param  array|\Traversable $haystack the array to be flattened
     * @param  string $value the field you want to be set as the values of the returned array
     * @param  string|null $key the field you want to be set as the keys of the returned array
     *
     * @throws ForeachableException
     *
     * @return array
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function flatten($haystack, $value, $key = null)
    {
        self::validateForeachable($haystack);

        $return = array();

        if (empty($key)) {
            foreach ($haystack as $e) {
                $return[] = $e[$value];
            }
        } else {
            foreach ($haystack as $e) {
                $return[$e[$key]] = $e[$value];
            }
        }

        return $return;
    }

    /**
     * Reassigns the keys of a 2-dimensional array from one of its second level field value
     *
     * @param  array $array the array to parse
     * @param  int|string $key the key to map
     *
     * @throws BusinessException
     * @throws ForeachableException
     *
     * @return array
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function reassignKey($array, $key)
    {
        self::validateForeachable($array);

        $return = array();
        if (count($array) == 0) {
            return $return;
        }

        $firstEntry = reset($array);

        if (empty($key) || !isset($firstEntry[$key])) {
            throw new BusinessException('Key "' . strval(
                            $key
                    ) . '" does not exist in the second dimension of this array');
        }

        foreach ($array as $e) {
            $return[$e[$key]] = $e;
        }

        return $return;
    }

    /**
     * Reassigns the keys of a 2-dimensional array from one of its second level field value
     *
     * @param  array $array the array to parse
     * @param  int|string $key the key to map
     *
     * @throws BusinessException
     * @throws ForeachableException
     *
     * @return array
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function reassignKeyFromObjects($array, $key)
    {
        self::validateForeachable($array);

        $return = array();
        if (count($array) == 0) {
            return $return;
        }

        $firstEntry = reset($array);

        $reflectionClass = new \ReflectionClass($firstEntry);

        $hasPublicAccess = isset($firstEntry->$key);

        $getter='';
        if(!$hasPublicAccess) {
            $getter = Strings::toGetter($key);
        }

        if (empty($key) || (!$hasPublicAccess && !$reflectionClass->hasMethod($getter))) {
            throw new BusinessException('Property "' . strval(
                            $key
                    ) . '" does not exist in the second dimension of this array');
        }

        foreach ($array as $e) {
            $theKey = $hasPublicAccess?$e->$key:$e->$getter();
            $return[$theKey] = $e;
        }

        return $return;
    }

    /**
     * Checks if a variable can be put into a foreach
     *
     * @param $param
     *
     * @throws ForeachableException
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    protected static function validateForeachable($param)
    {
        if (!is_array($param) && !($param instanceof \Traversable)) {
            throw new ForeachableException(Strings::getClassOrType($param));
        }
    }
}
