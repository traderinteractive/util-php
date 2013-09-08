<?php
/**
 * DominionEnterprises Framework
 */

namespace DominionEnterprises;

/**
 * Static class with various string utilities
 */
final class StringUtil
{
    /**
     * Replaces the format items in a specified string with the string representation of n specified objects.
     *
     * @param string $format A composit format string
     * @param mixed  $arg0   The first item to format
     * @param mixed  $arg2   The second item to format
     *
     * @return string Returns a copy of format in which the format items have been
     *                replaced by the string representations of arg0, arg1,... argN.
     *
     * @throws \InvalidArgumentException Thrown if $format is not a string
     * @throws \InvalidArgumentException Thrown if all arguments are not castable as strings or
     *                                  if less than two arguments are given
     */
    public static function format()
    {
        $arguments = func_get_args();

        Util::ensureTrue(count($arguments) >= 2, 'InvalidArgumentException', array('StringUtil::format() takes at least 2 arguments'));

        $format = array_shift($arguments);

        Util::throwIfNotType(array('string' => array($format)));

        set_error_handler('\DominionEnterprises\Util::raiseException');

        try {
            foreach ($arguments as $key => $value) {
                $format = str_replace("{{$key}}", (string)$value, $format);
            }
        } catch (\ErrorException $e) {
            restore_error_handler();
            throw new \InvalidArgumentException($e->getMessage(), 0, $e);
        }

        restore_error_handler();

        return $format;
    }

    /**
     * Checks if $string ends with $suffix and puts the rest of the $string in $nonSuffix.
     *
     * @param string $string The string to check
     * @param string $suffix The suffix to check for
     * @param mixed &$nonSuffix This is the part of the string that is not the suffix.
     *
     * @return bool whether the $string ended with $suffix or not.
     */
    public static function endsWith($string, $suffix, &$nonSuffix = null)
    {
        Util::throwIfNotType(array('string' => array($string, $suffix)));

        $suffixLength = strlen($suffix);

        if ($suffixLength === 0) {
            $nonSuffix = $string;
            return true;
        } elseif (empty($string)) {
            $nonSuffix = '';
            return false;
        }

        if (substr_compare($string, $suffix, -$suffixLength, $suffixLength) !== 0) {
            $nonSuffix = $string;
            return false;
        }

        $nonSuffix = substr($string, 0, -$suffixLength);
        return true;
    }
}
