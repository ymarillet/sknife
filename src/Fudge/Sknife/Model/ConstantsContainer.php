<?php
namespace Fudge\Sknife\Model;

/**
 * Abstract class for application wide and static sets of data (replaces a SQL table)
 * @author Yohann Marillet
 * @since 11/09/13
 */
abstract class ConstantsContainer implements ConstantsContainerInterface
{
    /**
     * @inheritdoc
     */
    public static function get($item)
    {
        static::test($item);

        return static::getAll()[$item];
    }

    /**
     * @inheritdoc
     */
    public static function isValid($item)
    {
        return isset(static::getAll()[$item]);
    }

    /**
     * @inheritdoc
     */
    public static function test($item)
    {
        $allItems = static::getAll();
        if (!isset($allItems[$item])) {
            throw new \InvalidArgumentException('The token "'.$item.'" is not in the list: ['.implode(',',array_keys($allItems)).']');
        }

        return true;
    }
}
