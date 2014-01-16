<?php
namespace Fudge\Sknife\Model;

/**
 * Container for application wide and static sets of data (replaces a SQL table)
 * @author Yohann Marillet
 * @since 11/09/13
 */
interface ConstantsContainerInterface
{
    /**
     * Gets all available items
     * @return array
     * @author Yohann Marillet
     */
    public static function getAll();

    /**
     * Gets an item's label
     * @param $item
     * @return mixed
     * @throws \InvalidArgumentException
     * @author Yohann Marillet
     */
    public static function get($item);

    /**
     * Determines if the parameter is a valid item
     * @param $item
     * @return bool
     * @author Yohann Marillet
     */
    public static function isValid($item);

    /**
     * Tests an item and throw an exception in case of error
     * @param $item
     * @throws \InvalidArgumentException
     * @author Yohann Marillet
     */
    public static function test($item);
}
