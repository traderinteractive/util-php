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
     * Simply returns an array value if the key isset,4 $default if it is not
     *
     * @param array $array the array to be searched
     * @param string|integer $key the key to search for
     * @param mixed $default the value to return if the $key is not found in $array or if the value of $key element is
     *                       null
     *
     * @return mixed array value or given default value
     */
    public static function getIfSet(array $array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * Sets destination array values to be the source values if the source key exist in the source array.
     *
     * @param array $source
     * @param array &$dest
     * @param array $keyMap mapping of dest keys to source keys. If $keyMap is associative, the keys will be the
     *                      destination keys. If numeric the values will be the destination keys
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
     * Sets destination array values to be the source values if the source key is set in the source array.
     *
     * @param array $source
     * @param array &$dest
     * @param array $keyMap mapping of dest keys to source keys. If $keyMap is associative, the keys will be the
     *                      destination keys. If numeric the values will be the destination keys
     *
     * @return void
     */
    public static function copyIfSet(array $source, array &$dest, array $keyMap)
    {
        foreach ($keyMap as $destKey => $sourceKey) {
            if (is_int($destKey)) {
                $destKey = $sourceKey;
            }

            if (isset($source[$sourceKey])) {
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
     * if $input = [
     *     ['key 1' => 'item 1 value 1', 'key 2' => 'item 1 value 2'],
     *     ['key 1' => 'item 2 value 1', 'key 2' => 'item 2 value 2'],
     *     ['key 1' => 'item 3 value 1'],
     * ]
     * and $key = 'key 2'
     * and $strictKeyCheck = false
     *
     * then return ['item 1 value 2', 'item 2 value 2']
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

        $projection = [];

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
        $result = [];
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
     * Each item's key is used as the key in the destination array so that keys are preserved.  Each resulting item in
     * the destination will be embedded into a field named by $fieldName.  Any items that don't have an entry in
     * destination already will be added, not skipped.
     *
     * For example, embedInto(['Joe', 'Sue'], 'lastName', [['firstName' => 'Billy'], ['firstName' => 'Bobby']]) will
     * return [['firstName' => 'Billy', 'lastName' => 'Joe'], ['firstName' => 'Bobby', 'lastName' => 'Sue']]
     *
     * @param array $items The items to embed into the result.
     * @param string $fieldName The name of the field to embed the items into.  This field must not exist in the
     *                          destination items already.
     * @param array $destination An optional array of arrays to embed the items into.  If this is not provided then
     *                           empty records are assumed and the new record will be created only containing
     *                           $fieldName.
     * @param bool $overwrite whether to overwrite $fieldName in $destination array
     *
     * @return array $destination, with all items in $items added using their keys, but underneath a nested $fieldName
     *               key.
     *
     * @throws \InvalidArgumentException if $fieldName was not a string
     * @throws \InvalidArgumentException if a value in $destination was not an array
     * @throws \Exception if $fieldName key already exists in a $destination array
     */
    public static function embedInto(array $items, $fieldName, array $destination = [], $overwrite = false)
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
                $destination[$key] = [$fieldName => $item];
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

    /**
     * Extracts an associative array from the given multi-dimensional array.
     *
     * @param array $input The multi-dimensional array.
     * @param string|int $keyIndex The index to be used as the key of the resulting single dimensional result array.
     * @param string|int $valueIndex The index to be used as the value of the resulting single dimensional result array.
     *                               If a sub array does not contain this element null will be used as the value.
     * @param string $duplicateBehavior Instruct how to handle duplicate resulting values, 'takeFirst', 'takeLast',
     *                                  'throw'
     *
     * @return array an associative array
     *
     * @throws \InvalidArgumentException Thrown if $input is not an multi-dimensional array
     * @throws \InvalidArgumentException Thrown if $keyIndex is not an int or string
     * @throws \InvalidArgumentException Thrown if $valueIndex is not an int or string
     * @throws \InvalidArgumentException Thrown if $duplicateBehavior is not 'takeFirst', 'takeLast', 'throw'
     * @throws \UnexpectedValueException Thrown if a $keyIndex value is not a string or integer
     * @throws \Exception Thrown if $duplicatedBehavior is 'throw' and duplicate entries are found.
     */
    public static function extract(array $input, $keyIndex, $valueIndex, $duplicateBehavior = 'takeLast')
    {
        if (!in_array($duplicateBehavior, ['takeFirst', 'takeLast', 'throw'])) {
            throw new \InvalidArgumentException("\$duplicateBehavior was not 'takeFirst', 'takeLast', or 'throw'");
        }

        if (!is_string($keyIndex) && !is_int($keyIndex)) {
            throw new \InvalidArgumentException('$keyIndex was not a string or integer');
        }

        if (!is_string($valueIndex) && !is_int($valueIndex)) {
            throw new \InvalidArgumentException('$valueIndex was not a string or integer');
        }

        $result = [];
        foreach ($input as $index => $array) {
            if (!is_array($array)) {
                throw new \InvalidArgumentException('$arrays was not a multi-dimensional array');
            }

            $key = self::get($array, $keyIndex);
            if (!is_string($key) && !is_int($key)) {
                throw new \UnexpectedValueException(
                    "Value for \$arrays[{$index}][{$keyIndex}] was not a string or integer"
                );
            }

            $value = self::get($array, $valueIndex);
            if (!array_key_exists($key, $result)) {
                $result[$key] = $value;
                continue;
            }

            if ($duplicateBehavior === 'throw') {
                throw new \Exception("Duplicate entry for '{$key}' found.");
            }

            if ($duplicateBehavior === 'takeLast') {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Returns the first set {@see isset()} value specified by the given array of keys.
     *
     * @param array $array The array containing the possible values.
     * @param array $keys Array of keys to search for. The first set value will be returned.
     * @param mixed $default The default value to return if no set value was found in the array.
     *
     * @return mixed Returns the found set value or the given default value.
     */
    public static function getFirstSet(array $array, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                return $array[$key];
            }
        }

        return $default;
    }

    /**
     * Partitions the given $input array into an array of $partitionCount sub arrays.
     *
     * This is a slight modification of the function suggested on
     * http://php.net/manual/en/function.array-chunk.php#75022. This method does not pad with empty partitions and
     * ensures positive partition count.
     *
     * @param array $input The array to partition.
     * @param int $partitionCount The maximum number of partitions to create.
     * @param bool $preserveKeys Flag to preserve numeric array indexes. Associative indexes are preserved by default.
     *
     * @return array A multi-dimensional array containing $partitionCount sub arrays.
     *
     * @throws \InvalidArgumentException Thrown if $partitionCount is not a positive integer.
     * @throws \InvalidArgumentException Thrown if $preserveKeys is not a boolean value.
     */
    public static function partition(array $input, $partitionCount, $preserveKeys = false)
    {
        if (!is_int($partitionCount) || $partitionCount < 1) {
            throw new \InvalidArgumentException('$partitionCount must be a positive integer');
        }

        if ($preserveKeys !== false && $preserveKeys !== true) {
            throw new \InvalidArgumentException('$preserveKeys must be a boolean value');
        }

        $inputLength = count($input);
        $partitionLength = floor($inputLength / $partitionCount);
        $partitionRemainder = $inputLength % $partitionCount;
        $partitions = [];
        $sliceOffset = 0;
        for ($partitionIndex = 0; $partitionIndex < $partitionCount && $sliceOffset < $inputLength; $partitionIndex++) {
            $sliceLength = ($partitionIndex < $partitionRemainder) ? $partitionLength + 1 : $partitionLength;
            $partitions[$partitionIndex] = array_slice($input, $sliceOffset, $sliceLength, $preserveKeys);
            $sliceOffset += $sliceLength;
        }

        return $partitions;
    }

    /**
     * Unsets all elements in the given $array specified by $keys
     *
     * @param array &$array The array containing the elements to unset.
     * @param array $keys Array of keys to unset.
     *
     * @return void
     */
    public static function unsetAll(array &$array, array $keys)
    {
        foreach ($keys as $key) {
            unset($array[$key]);
        }
    }

    /**
     * Convert all empty strings or strings that contain only whitespace to null in the given array
     *
     * @param array &$array The array containing empty strings
     *
     * @return void
     */
    public static function nullifyEmptyStrings(array &$array)
    {
        foreach ($array as &$value) {
            if (is_string($value) && trim($value) === '') {
                $value = null;
            }
        }
    }
}
