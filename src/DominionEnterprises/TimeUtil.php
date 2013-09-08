<?php
/**
 * Defines \DominionEnterprises\TimeUtil class
 */

namespace DominionEnterprises;

/**
 * Static class for time based utilities.
 */
final class TimeUtil
{
    /**
     * Converts unix timestamp into an ansi sql timestamp literal
     *
     * @param int $unixTimestamp
     * @return string ansi sql timestamp surrounded with parenthesis
     */
    public static function getAnsiSqlTimestamp($unixTimestamp)
    {
        Util::throwIfNotType(array('int' => $unixTimestamp));

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
