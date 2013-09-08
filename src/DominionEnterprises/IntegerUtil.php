<?php
/**
 * Defines the \DominionEnterprises\IntegerUtil class.
 *
 * @package DominionEnterprises
 */

namespace DominionEnterprises;

/**
 * Class of static helper functions for working with integers
 *
 * @package DominionEnterprises
 */
final class IntegerUtil
{
    /**
     * Returns null if $input is null, then @see filterInt
     *
     * @param string $input  A string containing the value to convert.
     * @param int    $result The resulting value
     *
     * @return int
     */
    public static function tryParse($input, &$result)
    {
        try {
            $result = self::parse($input);
            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        } catch (\OverflowException $e) {
            return false;
        }
    }

    /**
     * Converts $value to an integer strictly.
     *
     * $input must be an int or contain all digits, optionally prepended by a '+' or '-' and optionally surrounded by whitespace
     *
     * @param string|int $input
     *
     * @return int
     *
     * @throws \OverflowException
     * @throws \InvalidArgumentException
     */
    public static function parse($input)
    {
        if (is_int($input)) {
            return $input;
        }

        Util::throwIfNotType(array('string' => array($input)));

        $input = trim($input);

        if (strlen($input) === 0) {
            throw new \InvalidArgumentException("{$input} length is zero");
        }

        $stringToCheckDigits = $input;

        if ($input[0] === '-' || $input[0] === '+') {
            $stringToCheckDigits = substr($input, 1);
        }

        if (!ctype_digit($stringToCheckDigits)) {
            throw new \InvalidArgumentException(
                "{$input} does not contain all digits, optionally prepended by a '+' or '-' and optionally surrounded by whitespace"
            );
        }

        $phpIntMin = ~PHP_INT_MAX;

        $casted = (int)$input;

        if ($casted === PHP_INT_MAX && $input !== (string)PHP_INT_MAX) {
            throw new \OverflowException("{$input} was greater than a max int of " . PHP_INT_MAX);
        }

        if ($casted === $phpIntMin && $input !== (string)$phpIntMin) {
            throw new \OverflowException("{$input} was less than a min int of {$phpIntMin}");
        }

        return $casted;
    }
}
