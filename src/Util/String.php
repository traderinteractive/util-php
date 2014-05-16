<?php
/**
 * Defines \DominionEnterprises\Util\String class.
 */

namespace DominionEnterprises\Util;

/**
 * Static class with various string functions.
 */
final class String
{
    /**
     * Replaces the format items in a specified string with the string representation of n specified objects.
     *
     * @param string $format A composit format string
     * @param mixed $arg0 The first item to format
     * @param mixed $arg1 The second item to format
     *
     * @return string Returns a copy of format in which the format items have been
     *     replaced by the string representations of arg0, arg1,... argN.
     *
     * @throws \InvalidArgumentException Thrown if StringUtil::format() does not have at least 2 arguments
     * @throws \InvalidArgumentException Thrown if $format is not a string
     * @throws \InvalidArgumentException Thrown if all arguments are not castable as strings or
     *     if less than two arguments are given
     */
    public static function format()
    {
        $arguments = func_get_args();

        if (count($arguments) < 2) {
            throw new \InvalidArgumentException('StringUtil::format() takes at least 2 arguments');
        }

        $format = array_shift($arguments);

        if (!is_string($format)) {
            throw new \InvalidArgumentException('$format is not a string');
        }

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
     *
     * @throws \InvalidArgumentException if $string is not a string
     * @throws \InvalidArgumentException if $suffix is not a string
     */
    public static function endsWith($string, $suffix, &$nonSuffix = null)
    {
        if (!is_string($string)) {
            throw new \InvalidArgumentException('$string is not a string');
        }

        if (!is_string($suffix)) {
            throw new \InvalidArgumentException('$suffix is not a string');
        }

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

    /**
     * Truncates the string to the given length, with an ellipsis at the end.
     *
     * @param string $string The string to shorten.
     * @param int $maxLength The length to truncate the string to.  The result will not be longer than this, but may be shorter.
     * @param string $suffix The string to append when truncating.  Typically this will be an ellipsis.
     *
     * @return string The truncated string with the ellipsis included if truncation occured.
     *
     * @throws \InvalidArgumentException if $string is not a string
     * @throws \InvalidArgumentException if $maxLength is not an integer
     * @throws \InvalidArgumentException if $maxLength is negative
     * @throws \InvalidArgumentException if $suffix is not a string
     */
    public static function ellipsize($string, $maxLength, $suffix = '...')
    {
        if (!is_string($string)) {
            throw new \InvalidArgumentException('$string is not a string');
        }

        if (!is_int($maxLength)) {
            throw new \InvalidArgumentException('$maxLength is not an integer');
        }

        if ($maxLength < 0) {
            throw new \InvalidArgumentException('$maxLength is negative');
        }

        if (!is_string($suffix)) {
            throw new \InvalidArgumentException('$suffix is not a string');
        }

        if (strlen($string) <= $maxLength) {
            return $string;
        }

        $trimmedLength = $maxLength - strlen($suffix);
        $string = substr($string, 0, max(0, $trimmedLength));

        if ($string === '') {
            return substr($suffix, 0, $maxLength);
        }

        return $string . $suffix;
    }

    /**
     * Uppercases words using custom word delimiters.
     *
     * This is more flexible than normal php ucwords because that only treats space as a word delimiter.
     *
     * Here is an example:
     * <code>
     * <?php
     * $string = 'break-down o\'boy up_town you+me here now,this:place';
     *
     * echo String::ucwords($string);
     * // Break-Down O\'Boy Up_Town You+Me Here Now,This:Place
     *
     * echo String::ucwords($string, '- ');
     * // Break-Down O\'boy Up_town You+me Here Now,this:place
     * ?>
     * </code>
     *
     * @param string $string The string to titleize.
     * @param string $delimiters The characters to treat as word delimiters.
     *
     * @return string The titleized string.
     *
     * @throws \InvalidArgumentException if $string is not a string
     * @throws \InvalidArgumentException if $delimiters is not a string
     */
    public static function ucwords($string, $delimiters = "-_+' \n\t\r\0\x0B:/,.")
    {
        if (!is_string($string)) {
            throw new \InvalidArgumentException('$string is not a string');
        }

        if (!is_string($delimiters)) {
            throw new \InvalidArgumentException('$delimiters is not a string');
        }

        if ($delimiters === '') {
            return $string;
        }

        return preg_replace_callback(
            '/[^' . preg_quote($delimiters, '/') . ']+/',
            function($matches) {
                return ucfirst($matches[0]);
            },
            $string
        );
    }
}
