<?php
/**
 * Defines the \DominionEnterprises\FloatUtil class.
 */

namespace DominionEnterprises;

/**
 * Class of static float helper functions.
 */
final class FloatUtil
{
    /**
     * Converts $input to a float strictly.
     *
     * @param string|float $input A string containing the value to convert.
     * @param float        $result The resulting value
     *
     * @return boolean
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

    /**
     * Converts $input to a float strictly.
     *
     * @see is_numeric
     *
     * @param string|int|float $input
     *
     * @return float
     *
     * @throws \OverflowException
     * @throws \UnexpectedValueException
     */
    public static function parse($input)
    {
        if (is_float($input)) {
            return $input;
        }

        if (is_int($input)) {
            return (float)$input;
        }

        Util::throwIfNotType(array('string' => array($input)));

        $input = trim($input);

        if (!is_numeric($input)) {
            throw new \UnexpectedValueException("{$input} does not pass is_numeric");
        }

        $input = strtolower($input);

        //This is the only case (that we know of) where is_numeric does not return correctly castable float
        if (strpos($input, 'x') !== false) {
            throw new \UnexpectedValueException("{$input} is hex format");
        }

        $casted = (float)$input;

        if (is_infinite($casted)) {
            throw new \OverflowException("{$input} overflow");
        }

        return $casted;
    }
}
