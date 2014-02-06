<?php
namespace Fudge\Sknife\Util;

/**
 * Strings related util functions
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 09/09/2013
 */
class Strings
{
    private function __construct()
    {
        //static functions only - no constructor
    }

    const OPT_CAMELIZE_NONE = 0;
    const OPT_CAMELIZE_LOWERCASE_FIRST = 1;

    /**
     * Camelize a string
     *
     * @param string $string
     * @param string $start_chars the first non alpha-numeric characters will be converted into this string
     * @param int $options
     *
     * @return string
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function toCamel($string, $start_chars = '_', $options = self::OPT_CAMELIZE_NONE)
    {
        $s = preg_replace('#[^a-z0-9]#i', ' ', $string);
        $s = ucwords(mb_strtolower($s));
        $nb_first_chars = strspn($s, ' ');
        $s = trim($s);
        if ($options && self::OPT_CAMELIZE_LOWERCASE_FIRST) {
            $s = lcfirst($s);
        }
        $s = str_replace(' ', '', $s);
        $i = 0;
        if (!empty($start_chars)) {
            while ($i < $nb_first_chars) {
                $s = $start_chars . $s;
                $i++;
            }
        }

        return $s;
    }

    /**
     * Alias for self::toCamel($string, self::OPT_CAMELIZE_LOWERCASE_FIRST)
     *
     * @see self::toCamel
     *
     * @param string $string
     * @param string $start_chars
     *
     * @return string
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function toLowerCamel($string, $start_chars = '_')
    {
        return self::toCamel($string, $start_chars, self::OPT_CAMELIZE_LOWERCASE_FIRST);
    }

    /**
     * Gets the class or the type (if not an object) of the parameter
     *
     * @param  mixed  $value
     * @return string
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function getClassOrType($value)
    {
        $type = gettype($value);

        return ($type == 'object' ? get_class($value) : $type);
    }

    /**
     * Slugify a string (i.e. converts any non authorized char into a specified char)
     *
     * @param string $string string to slugify
     * @param string $slug_char replacement character
     * @param string $regex unauthorized character
     * @param callable|string $callback callback function which is first called to pre-transform the string
     *
     * @return string
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function slugify($string, $slug_char = '-', $regex = '#[^a-z0-9]#i', $callback = 'mb_strtolower')
    {
        $return = $callback($string);
        $return = preg_replace($regex, $slug_char, $return);

        return $return;
    }

    /**
     * Unslugify a string (i.e. converts a 'slugged' string into something more human-readable)
     *
     * @param string $string
     * @param string $slug_char
     * @param string $replace_slug_char
     * @param callback|string $callback
     *
     * @return string
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function unslugify($string, $slug_char = '-', $replace_slug_char=' ', $callback='ucfirst')
    {
        $return = str_replace($slug_char, $replace_slug_char, $string);
        if (!empty($callback)) {
            $return = $callback($return);
        }

        return $return;
    }

    /**
     * Get the standardized getter name for a field name in an object
     *
     * @param  string $field
     * @return string
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function toGetter($field)
    {
        return 'get' . self::toCamel($field);
    }

    /**
     * Get the standardized setter name for a field name in an object
     *
     * @param  string $field
     * @return string
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function toSetter($field)
    {
        return 'set' . self::toCamel($field);
    }

}
