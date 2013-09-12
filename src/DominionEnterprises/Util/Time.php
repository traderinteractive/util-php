<?php
/**
 * Defines \DominionEnterprises\Util\Time class.
 */

namespace DominionEnterprises\Util;

/**
 * Static class for time based functions.
 */
final class Time
{
    /**
     * Converts unix timestamp into an ansi sql timestamp literal
     *
     * @param int $unixTimestamp
     *
     * @return string ansi sql timestamp surrounded with parenthesis
     *
     * @throws \InvalidArgumentException if $unixTimestamp was not an int
     */
    public static function getAnsiSqlTimestamp($unixTimestamp)
    {
        if (!is_int($unixTimestamp)) {
            throw new \InvalidArgumentException('$unixTimestamp was not an int');
        }

        return "(TIMESTAMP'" . date('Y-m-d H:i:s', $unixTimestamp) . "')";
    }

    /**
     * Get current unix time in milliseconds
     *
     * @return int the current unix time
     */
    public static function inMillis()
    {
        return (int)(microtime(true) * 1000);
    }
}
