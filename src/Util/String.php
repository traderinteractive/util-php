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
     * Takes a string and truncates is if longer than count adding ellipsis
     *
     * @param string $string original string to be shortened
     * @param int $maxLength maximum length of the string to be returned
     * @param string $suffix optional suffix to append to the returned value
     *
     * @return string
     *
     * @throws \InvalidArgumentException if $string is not a string
     * @throws \InvalidArgumentException if $maxLength is not an integer
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

        if (!is_string($suffix)) {
            throw new \InvalidArgumentException('$suffix is not a string');
        }

        $trimmedLength = $maxLength - strlen($suffix);
        if ($trimmedLength > 0 && strlen($string) > $trimmedLength) {
            $string = substr_replace($string, $suffix, $trimmedLength);
        }

        return $string;
    }

    /**
     * Uppercases words that follow a list of word delimiter markers.
     * Takes a string and optional array of markers which are to be used to uppercase characters that follow them. More flexible than
     * normal php ucwords because that will just capitalize character after a space.
     *
     * Here is an inline example:
     * <code>
     * <?php
     * $string = 'break-down o\'boy up_town you+me here now,this:place';
     * echo String::ucwords($string);
     * // Should output the following: Break-Down O\'Boy Up_Town You+Me Here Now,This:Place
     * echo String::ucwords($string, array('\A','-','\s));
     * // Should output the following: Break-Down O\'boy Up_town You+me Here Now,this:place
     * ? >
     * </code>
     *
     * @param string $string original string to be uppercased at word starts
     * @param array $markers optional array of regular expression markers
     *
     * @return string
     *
     * @throws \InvalidArgumentException if $string is not a string
     * @throws \InvalidArgumentException if $markers is not an array
     */
    public static function ucwords($string, $markers = array('\A', '\-', '\_', '\+', "\'", '\s', '\:', '\/', '\,', '\.'))
    {
        if (!is_string($string)) {
            throw new \InvalidArgumentException('$string is not a string');
        }

        if (!is_array($markers)) {
            throw new \InvalidArgumentException('$markers is not an array');
        }

        $_approvedMarkers = array('\A', '\-', '\_', '\+', "\'", '\s', '\:', '\/', '\,', '\.');

        $sanitizedMarkers = array();
        foreach ($markers as $marker) {
            $sanitizedMarkers[] = in_array($marker, $_approvedMarkers) ? $marker : preg_quote($marker, '/');
        }

        return preg_replace_callback(
            '/[^' . implode('', $sanitizedMarkers) . ']+/',
            function($matches) {
                return ucfirst($matches[0]);
            },
            $string
        );
    }
}
