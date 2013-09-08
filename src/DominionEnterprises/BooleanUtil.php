<?php
/**
 * Defines the \DominionEnterprises\BooleanUtil class.
 */

namespace DominionEnterprises;

/**
 * Class of static helper functions for working with booleans
 */
final class BooleanUtil
{
    /**
     * Converts $input to a boolean strictly.
     *
     * $input must be a boolean or 'true' or 'false' disregarding case.
     *
     * @param string|bool $input the value to parse
     *
     * @return bool the parsed value
     *
     * @throws \UnexpectedValueException \Exception
     */
    public static function parse($input)
    {
        if (is_bool($input)) {
            return $input;
        }

        Util::throwIfNotType(array('string' => array($input)));

        $input = strtolower(trim($input));

        if ($input === 'true') {
            return true;
        } elseif ($input === 'false') {
            return false;
        }

        throw new \UnexpectedValueException("{$input} is not 'true' or 'false' disregarding case");
    }

    /**
     * Converts $input to a boolean strictly.
     *
     * $input must be a boolean or 'true' or 'false' disregarding case.
     *
     * @param string|bool $input A string containing the value to convert.
     * @param bool $result The resulting value
     *
     * @return bool TRUE if the value could be parsed, otherwise false
     */
    public static function tryParse($input, &$result)
    {
        try {
            $result = self::parse($input);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
