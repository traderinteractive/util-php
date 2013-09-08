<?php
/**
 * DominionEnterprises Framework
 */

namespace DominionEnterprises;

/**
 * Static class with various application utilities
 */
final class Util
{
    /**
     * Returns exception info in array.
     *
     * @param \Exception $e the exception to return info on
     *
     * @return array like:
     * <pre>
     * array(
     *     'type' => 'Exception',
     *     'message' => 'a message',
     *     'code' => 0,
     *     'file' => '/somePath',
     *     'line' => 434,
     *     'trace' => 'a stack trace',
     * )
     * </pre>
     */
    public static function getExceptionInfo(\Exception $e)
    {
        return array(
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        );
    }

    /**
     * Ensures that $valueToEnsure is equal to $valueToCheck or it throws
     *
     * Can be used like: $result = ensure(true, is_string('boo'))
     * Or like: $result = ensure(true, is_string('boo'), 'the message')
     * Or like: $result = ensure(true, is_string('boo'), new MyException('the message', 2))
     * Or like: $result = ensure(true, is_string('boo'), 'MyException', array('the message', 2))
     *
     * @param mixed $valueToEnsure the value to throw on if $valueToCheck equals it
     * @param mixed $valueToCheck the value to check against $valueToEnsure
     * @param string|\Exception|null $exception a string for an Exception or an Exception itself or null
     * @param array|null $exceptionArgs arguments to pass to a new instance of $exception. If using this parameter make sure these arguments
     * match the constructor for an exception of type $exception. This parameter is mainly to avoid unecessary Exception object creation.
     *
     * @return mixed returns $valueToCheck
     *
     * @throws \Exception if $valueToEnsure !== $valueToCheck
     * @throws \InvalidArgumentException if $exception was not a string, Exception or null
     */
    public static function ensure($valueToEnsure, $valueToCheck, $exception = null, array $exceptionArgs = null)
    {
        if ($valueToEnsure === $valueToCheck) {
            return $valueToCheck;
        }

        if ($exception === null) {
            throw new \Exception("'{$valueToEnsure}' did not equal '{$valueToCheck}'");
        } elseif (is_string($exception)) {
            if ($exceptionArgs === null) {
                throw new \Exception($exception);
            } else {
                $reflectionClass = new \ReflectionClass($exception);
                throw $reflectionClass->newInstanceArgs($exceptionArgs);
            }
        } elseif ($exception instanceof \Exception) {
            throw $exception;
        } else {
            throw new \InvalidArgumentException('$exception was not a string, Exception or null');
        }
    }

    /**
     * Ensures that $valueToThrowOn is not equal to $valueToCheck or it throws
     *
     * Can be used like: $curl = ensureNot(false, curl_init('boo'))
     * Or like: $curl = ensureNot(false, curl_init('boo'), 'bad message')
     * Or like: $curl = ensureNot(false, curl_init('boo'), new MyException('bad message', 2))
     * Or like: $curl = ensureNot(false, curl_init('boo'), 'MyException', array('bad message', 2))
     *
     * @param mixed $valueToThrowOn the value to throw on if $valueToCheck equals it
     * @param mixed $valueToCheck the value to check against $valueToThrowOn
     * @param string|\Exception|null $exception a string for an Exception or an Exception itself or null
     * @param array|null $exceptionArgs arguments to pass to a new instance of $exception. If using this parameter make sure these arguments
     * match the constructor for an exception of type $exception. This parameter is mainly to avoid unecessary Exception object creation.
     *
     * @return mixed returns $valueToCheck
     *
     * @throws \Exception if $valueToThrowOn === $valueToCheck
     * @throws \InvalidArgumentException if $exception was not a string, Exception or null
     */
    public static function ensureNot($valueToThrowOn, $valueToCheck, $exception = null, array $exceptionArgs = null)
    {
        if ($valueToThrowOn !== $valueToCheck) {
            return $valueToCheck;
        }

        if ($exception === null) {
            throw new \Exception("'{$valueToThrowOn}' equals '{$valueToCheck}'");
        } elseif (is_string($exception)) {
            if ($exceptionArgs === null) {
                throw new \Exception($exception);
            } else {
                $reflectionClass = new \ReflectionClass($exception);
                throw $reflectionClass->newInstanceArgs($exceptionArgs);
            }
        } elseif ($exception instanceof \Exception) {
            throw $exception;
        } else {
            throw new \InvalidArgumentException('$exception was not a string, Exception or null');
        }
    }

    /**
     * Same as @see ensure() with true for $valueToEnsure
     *
     * If used in tight loops or long lists its better to use just ensure() for performance
     */
    public static function ensureTrue($valueToCheck, $exception = null, array $exceptionArgs = null)
    {
        return self::ensure(true, $valueToCheck, $exception, $exceptionArgs);
    }

    /**
     * Same as @see ensureNot() with false for $valueToThrowOn
     *
     * If used in tight loops or long lists its better to use just ensureNot() for performance
     */
    public static function ensureNotFalse($valueToCheck, $exception = null, array $exceptionArgs = null)
    {
        return self::ensureNot(false, $valueToCheck, $exception, $exceptionArgs);
    }

    /**
     * Throws a new ErrorException based on the error information provided. To be
     * used as a callback for @see set_error_handler()
     *
     * @throws \ErrorException
     */
    public static function raiseException($level, $message, $file = null, $line = null)
    {
        if (error_reporting() === 0) {
            return false;
        }

        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    /**
     * Function to ensure all arguments passed are not null
     *
     * @param string $arg1 the first value to check
     * @param string $arg2,...,$argN additional values to check
     *
     * @return void
     *
     * @throws \InvalidArgumentException Thrown if any of the given arguments is null
     */
    public static function throwIfNull()
    {
        foreach (func_get_args() as $key => $value) {
            $num = $key + 1;
            if ($value === null) {
                throw new \InvalidArgumentException("Argument {$num} is null");
            }
        }
    }

    /**
     * Function to ensure all arguments passed are not null and not strings consisting of only whitespace
     *
     * @param string $arg1 the first value to check
     * @param string $arg2,...,$argN additional values to check
     *
     * @return void
     *
     * @throws \InvalidArgumentException Thrown if any of the given arguments is null or a string consisting of only whitespace
     */
    public static function throwIfNullOrWhiteSpace()
    {
        foreach (func_get_args() as $key => $value) {
            $num = $key + 1;
            if ($value === null) {
                throw new \InvalidArgumentException("Argument {$num} is null");
            }

            if (is_string($value) && trim($value) == '') {
                throw new \InvalidArgumentException("Argument {$num} is whitespace");
            }
        }
    }

    /**
     * Throws an exception if specified variables are not of given types.
     *
     * @param array $typesToVariables like array('string' => array($var1, $var2), 'int' => array($var1, $var2))
     *                                or array('string' => $var1, 'integer' => array(1, $var2))
     *                                supported types are the suffixes of the is_* functions such as string for is_string and int for is_int
     * @param bool $failOnWhitespace whether to fail strings if they are whitespace
     * @param bool $allowNulls whether to allow null values to pass through
     *
     * @return void
     *
     * @throws \InvalidArgumentException if a key in $typesToVariables was not a string
     * @throws \InvalidArgumentException if a key in $typesToVariables did not have an is_ function
     * @throws \InvalidArgumentException if a variable is not of correct type
     * @throws \InvalidArgumentException if a variable is whitespace and $failOnWhitespace is set
     * @throws \InvalidArgumentException if $failOnWhitespace was not a bool
     * @throws \InvalidArgumentException if $allowNulls was not a bool
     */
    public static function throwIfNotType(array $typesToVariables, $failOnWhitespace = false, $allowNulls = false)
    {
        if ($allowNulls !== false && $allowNulls !== true) {
            throw new \InvalidArgumentException('$allowNulls was not a bool');
        }

        if ($failOnWhitespace !== false && $failOnWhitespace !== true) {
            throw new \InvalidArgumentException('$failOnWhitespace was not a bool');
        }

        foreach ($typesToVariables as $type => $variablesOrVariable) {
            $variables = array($variablesOrVariable);
            if (is_array($variablesOrVariable)) {
                $variables = $variablesOrVariable;
            }

            //cast ok since an integer won't match any of the cases.
            //the similiar code in the cases is an optimization for those type where faster checks can be made.
            switch ((string)$type) {
                case 'bool':
                    foreach ($variables as $i => $variable) {
                        //using the continue here not negative checks to make use of short cutting optimization.
                        if ($variable === false || $variable === true || ($allowNulls && $variable === null)) {
                            continue;
                        }

                        throw new \InvalidArgumentException("variable at position '{$i}' was not a boolean");
                    }

                    break;
                case 'null':
                    foreach ($variables as $i => $variable) {
                        if ($variable !== null) {
                            throw new \InvalidArgumentException("variable at position '{$i}' was not null");
                        }
                    }

                    break;
                case 'string':
                    foreach ($variables as $i => $variable) {
                        if (is_string($variable)) {
                            if ($failOnWhitespace && trim($variable) === '') {
                                throw new \InvalidArgumentException("variable at position '{$i}' was whitespace");
                            }

                            continue;
                        }

                        if ($allowNulls && $variable === null) {
                            continue;
                        }

                        throw new \InvalidArgumentException("variable at position '{$i}' was not a '{$type}'");
                    }

                    break;
                case 'array':
                case 'callable':
                case 'double':
                case 'float':
                case 'int':
                case 'integer':
                case 'long':
                case 'numeric':
                case 'object':
                case 'real':
                case 'resource':
                case 'scalar':
                    $isFunction = "is_{$type}";
                    foreach ($variables as $i => $variable) {
                        if ($isFunction($variable) || ($allowNulls && $variable === null)) {
                            continue;
                        }

                        throw new \InvalidArgumentException("variable at position '{$i}' was not a '{$type}'");
                    }

                    break;
                default:
                    throw new \InvalidArgumentException('a type was not one of the is_ functions');
            }
        }
    }
}
