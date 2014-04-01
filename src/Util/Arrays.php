<?php
/**
 * Defines the \DominionEnterprises\Util\Arrays class.
 */

namespace DominionEnterprises\Util;

/**
 * Class of static array utility functions.
 */
final class Arrays
{
    /**
     * Simply returns an array value if the key exist or null if it does not.
     *
     * @param array $array the array to be searched
     * @param string|integer $key the key to search for
     * @param mixed $default the value to return if the $key is not found in $array
     *
     * @return mixed array value or given default value
     */
    public static function get(array $array, $key, $default = null)
    {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }

    /**
     * Sets destination array values to be the source values if the source key exist in the source array.
     *
     * @param array $source
     * @param array &$dest
     * @param array $keyMap mapping of dest keys to source keys. If $keyMap is associative, the keys will be the destination keys. If numeric
     *                      the values will be the destination keys
     *
     * @return void
     */
    public static function copyIfKeysExist(array $source, array &$dest, array $keyMap)
    {
        foreach ($keyMap as $destKey => $sourceKey) {
            if (is_int($destKey)) {
                $destKey = $sourceKey;
            }

            if (array_key_exists($sourceKey, $source)) {
                $dest[$destKey] = $source[$sourceKey];
            }
        }
    }

    /**
     * Returns true and fills $value if $key exists in $array, otherwise fills $value with null and returns false
     *
     * @param array $array The array to pull from
     * @param string|integer $key The key to get
     * @param mixed &$value The value to set
     *
     * @return bool true if $key was found and filled in $value, false if $key was not found and $value was set to null
     */
    public static function tryGet(array $array, $key, &$value)
    {
        if ((is_string($key) || is_int($key)) && array_key_exists($key, $array)) {
            $value = $array[$key];
            return true;
        }

        $value = null;
        return false;
    }

    /**
     * Projects values of a key into an array.
     *
     * if $input = array(
     *     array('key 1' => 'item 1 value 1', 'key 2' => 'item 1 value 2'),
     *     array('key 1' => 'item 2 value 1', 'key 2' => 'item 2 value 2'),
     *     array('key 1' => 'item 3 value 1'),
     * )
     * and $key = 'key 2'
     * and $strictKeyCheck = false
     *
     * then return array('item 1 value 2', 'item 2 value 2')
     *
     * but if $strictKeyCheck = true then an InvalidArgumentException occurs since 'key 2' wasnt in item 3
     *
     * @param array $input the array to project from
     * @param string|integer $key the key which values we are to project
     * @param boolean $strictKeyCheck ensure key is in each $input array or not
     *
     * @return array the projection
     *
     * @throws \InvalidArgumentException if $strictKeyCheck was not a bool
     * @throws \InvalidArgumentException if a value in $input was not an array
     * @throws \InvalidArgumentException if a key was not in one of the $input arrays
     */
    public static function project(array $input, $key, $strictKeyCheck = true)
    {
        if ($strictKeyCheck !== false && $strictKeyCheck !== true) {
            throw new \InvalidArgumentException('$strictKeyCheck was not a bool');
        }

        $projection = array();

        foreach ($input as $itemKey => $item) {
            if (!is_array($item)) {
                throw new \InvalidArgumentException('a value in $input was not an array');
            }

            if (array_key_exists($key, $item)) {
                $projection[$itemKey] = $item[$key];
            } elseif ($strictKeyCheck) {
                throw new \InvalidArgumentException('key was not in one of the $input arrays');
            }
        }

        return $projection;
    }

    /**
     * Returns a sub set of the given $array based on the given $conditions
     *
     * @param array[] $array an array of arrays to be checked
     * @param array $conditions array of key/value pairs to filter by
     *
     * @return array the subset
     *
     * @throws \InvalidArgumentException if a value in $array was not an array
     */
    public static function where(array $array, array $conditions)
    {
        $result = array();
        foreach ($array as $item) {
            if (!is_array($item)) {
                throw new \InvalidArgumentException('a value in $array was not an array');
            }

            foreach ($conditions as $key => $value) {
                if (!array_key_exists($key, $item) || $item[$key] !== $value) {
                    continue 2; // continue to the next item in $array
                }
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Takes each item and embeds it into the destination array, returning the result.
     *
     * Each item's key is used as the key in the destination array so that keys are preserved.  Each resulting item in the destination will be
     * embedded into a field named by $fieldName.  Any items that don't have an entry in destination already will be added, not skipped.
     *
     * For example, embedInto(['Joe', 'Sue'], 'lastName', [['firstName' => 'Billy'], ['firstName' => 'Bobby']]) will return
     * [['firstName' => 'Billy', 'lastName' => 'Joe'], ['firstName' => 'Bobby', 'lastName' => 'Sue']]
     *
     * @param array $items The items to embed into the result.
     * @param string $fieldName The name of the field to embed the items into.  This field must not exist in the destination items already.
     * @param array $destination An optional array of arrays to embed the items into.  If this is not provided then empty records are assumed
     *     and the new record will be created only containing $fieldName.
     * @param bool $overwrite whether to overwrite $fieldName in $destination array
     *
     * @return array $destination, with all items in $items added using their keys, but underneath a nested $fieldName key.
     *
     * @throws \InvalidArgumentException if $fieldName was not a string
     * @throws \InvalidArgumentException if a value in $destination was not an array
     * @throws \Exception if $fieldName key already exists in a $destination array
     */
    public static function embedInto(array $items, $fieldName, array $destination = array(), $overwrite = false)
    {
        if (!is_string($fieldName)) {
            throw new \InvalidArgumentException('$fieldName was not a string');
        }

        if ($overwrite !== false && $overwrite !== true) {
            throw new \InvalidArgumentException('$overwrite was not a bool');
        }

        foreach ($items as $key => $item) {
            if (array_key_exists($key, $destination)) {
                if (!is_array($destination[$key])) {
                    throw new \InvalidArgumentException('a value in $destination was not an array');
                }

                if (!$overwrite && array_key_exists($fieldName, $destination[$key])) {
                    throw new \Exception('$fieldName key already exists in a $destination array');
                }

                $destination[$key][$fieldName] = $item;
            } else {
                $destination[$key] = array($fieldName => $item);
            }
        }

        return $destination;
    }

    /**
     * Fills the given $template array with values from the $source array
     *
     * @param array $template the array to be filled
     * @param array $source the array to fetch values from
     *
     * @return array Returns a filled version of $template
     */
    public static function fillIfKeysExist(array $template, array $source)
    {
        $result = $template;
        foreach ($template as $key => $value) {
            if (array_key_exists($key, $source)) {
                $result[$key] = $source[$key];
            }
        }

        return $result;
    }
}
