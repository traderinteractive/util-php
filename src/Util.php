<?php
/**
 * Defines the \DominionEnterprises\Util class.
 */

namespace DominionEnterprises;

/**
 * Static class with various application functions.
 */
final class Util
{
    private static $_exceptionAliases = ['http' => '\DominionEnterprises\HttpException'];

    /**
     * Returns exception info in array.
     *
     * @param \Exception $e the exception to return info on
     *
     * @return array like:
     * <pre>
     * [
     *     'type' => 'Exception',
     *     'message' => 'a message',
     *     'code' => 0,
     *     'file' => '/somePath',
     *     'line' => 434,
     *     'trace' => 'a stack trace',
     * ]
     * </pre>
     */
    public static function getExceptionInfo(\Exception $e)
    {
        return [
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];
    }

    /**
     * Ensures that $valueToEnsure is equal to $valueToCheck or it throws
     *
     * Can be used like: $result = ensure(true, is_string('boo'))
     * Or like: $result = ensure(true, is_string('boo'), 'the message')
     * Or like: $result = ensure(true, is_string('boo'), 'MyException', ['the message', 2])
     * Or like: $result = ensure(true, is_string('boo'), new MyException('the message', 2))
     *
     * @param mixed $valueToEnsure the value to throw on if $valueToCheck equals it
     * @param mixed $valueToCheck the value to check against $valueToEnsure
     * @param null|string|\Exception $exception null, a fully qualified exception class name, string for an Exception message, or an Exception.
     *     The fully qualified exception class name could also be an alias in getExceptionAliases()
     * @param array|null $exceptionArgs arguments to pass to a new instance of $exception. If using this parameter make sure these arguments
     *     match the constructor for an exception of type $exception.
     *
     * @return mixed returns $valueToCheck
     *
     * @throws \Exception if $valueToEnsure !== $valueToCheck
     * @see throwProvidedException
     */
    public static function ensure($valueToEnsure, $valueToCheck, $exception = null, array $exceptionArgs = null)
    {
        if ($valueToEnsure === $valueToCheck) {
            return $valueToCheck;
        }

        $message = sprintf('\'%s\' did not equal \'%s\'', $valueToEnsure, $valueToCheck);
        return self::throwProvidedException($exception, $exceptionArgs, $message);
    }

    /**
     * Ensures that $valueToThrowOn is not equal to $valueToCheck or it throws
     *
     * Can be used like: $curl = ensureNot(false, curl_init('boo'))
     * Or like: $curl = ensureNot(false, curl_init('boo'), 'bad message')
     * Or like: $curl = ensureNot(false, curl_init('boo'), 'MyException', ['bad message', 2])
     * Or like: $curl = ensureNot(false, curl_init('boo'), new MyException('bad message', 2))
     *
     * @param mixed $valueToThrowOn the value to throw on if $valueToCheck equals it
     * @param mixed $valueToCheck the value to check against $valueToThrowOn
     * @param null|string|\Exception $exception null, a fully qualified exception class name, string for an Exception message, or an Exception.
     *     The fully qualified exception class name could also be an alias in getExceptionAliases()
     * @param array|null $exceptionArgs arguments to pass to a new instance of $exception. If using this parameter make sure these arguments
     *     match the constructor for an exception of type $exception.
     *
     * @return mixed returns $valueToCheck
     *
     * @throws \Exception if $valueToThrowOn === $valueToCheck
     * @see throwProvidedException
     */
    public static function ensureNot($valueToThrowOn, $valueToCheck, $exception = null, array $exceptionArgs = null)
    {
        if ($valueToThrowOn !== $valueToCheck) {
            return $valueToCheck;
        }

        $message = sprintf('\'%s\' equals \'%s\'', $valueToThrowOn, $valueToCheck);
        return self::throwProvidedException($exception, $exceptionArgs, $message);
    }

    /**
     * Throws the provided exception.
     *
     * @param null|string|\Exception $exception null, a fully qualified exception class name, string for an Exception message, or an Exception.
     *     The fully qualified exception class name could also be an alias in getExceptionAliases()
     * @param array|null $exceptionArgs arguments to pass to a new instance of $exception. If using this parameter make sure these arguments
     *     match the constructor for an exception of type $exception.
     * @param string $message message to be thrown if the first parameter is null
     * @throws \InvalidArgumentException if $exception was not null, a string, or an Exception
     * @throws \Exception
     */
    public static function throwProvidedException($exception = null, array $exceptionArgs = null, $message = '')
    {
        if ($exception === null) {
            throw new \Exception($message);
        }

        if (is_string($exception)) {
            if ($exceptionArgs === null) {
                throw new \Exception($exception);
            }

            if (array_key_exists($exception, self::$_exceptionAliases)) {
                $exception = self::$_exceptionAliases[$exception];
            }

            try {
                $reflectionClass = new \ReflectionClass($exception);
            } catch (\ReflectionException $ex) {
                throw new \InvalidArgumentException('$exception is of an invalid type');
            }

            /** @var $newException \Exception */
            $newException = $reflectionClass->newInstanceArgs($exceptionArgs);
            throw $newException;
        }

        if ($exception instanceof \Exception) {
            throw $exception;
        }

        throw new \InvalidArgumentException('$exception was not null, a string, or an Exception');
    }

    /**
     * Throws a new ErrorException based on the error information provided. To be
     * used as a callback for @see set_error_handler()
     *
     * @param int $level level of the error raised
     * @param string $message error message
     * @param null|string $file filename the error was raised in
     * @param null|int $line line number the error was raised at
     * @throws \ErrorException
     * @return mixed false|null
     */
    public static function raiseException($level, $message, $file = null, $line = null)
    {
        if (error_reporting() === 0) {
            return false;
        }

        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    /**
     * Throws an exception if specified variables are not of given types.
     *
     * @param array $typesToVariables like ['string' => [$var1, $var2], 'int' => [$var1, $var2]]
     *     or ['string' => $var1, 'integer' => [1, $var2]]. Supported types are the suffixes of the is_* functions such as string
     *     for is_string and int for is_int
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
        if (!is_bool($allowNulls)) {
            throw new \InvalidArgumentException('$allowNulls was not a bool');
        }

        if (!is_bool($failOnWhitespace)) {
            throw new \InvalidArgumentException('$failOnWhitespace was not a bool');
        }

        foreach ($typesToVariables as $type => $variablesOrVariable) {
            $variables = [$variablesOrVariable];
            if (is_array($variablesOrVariable)) {
                $variables = $variablesOrVariable;
            }

            //cast ok since an integer won't match any of the cases.
            //the similar code in the cases is an optimization for those type where faster checks can be made.
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

    /**
     * Return the exception aliases.
     *
     * @return array array where keys are aliases and values are strings to a fully qualified exception class names.
     */
    public static function getExceptionAliases()
    {
        return self::$_exceptionAliases;
    }

    /**
     * Set the exception aliases.
     *
     * @param array $aliases array where keys are aliases and values are strings to a fully qualified exception class names.
     */
    public static function setExceptionAliases(array $aliases)
    {
        self::$_exceptionAliases = $aliases;
    }

    /**
     * Call a static (possibly non public) method through reflection. Intended for use in testing, but should only use on methods as one would
     * use a package level method in another language, since php does not have the feature.
     *
     * @param string|array callable to be called
     * @param array $args arguments to pass to the reflected method
     *
     * @return mixed result of the reflected method call
     *
     * @throws \InvalidArgumentException if $method was not a string
     * @throws \InvalidArgumentException if $method was not static
     */
    public static function callStatic($method, array $args = [])
    {
        if (!is_callable($method, true)) {
            throw new \InvalidArgumentException('$method was not a callable');
        }

        if (is_array($method)) {
            $method = new \ReflectionMethod($method[0], $method[1]);
        } else {
            $method = new \ReflectionMethod($method);
        }

        if (!$method->isStatic()) {
            throw new \InvalidArgumentException('$method was not static');
        }

        $method->setAccessible(true);
        return $method->invokeArgs(null, $args);
    }
}
