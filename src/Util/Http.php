<?php
/**
 * Defines the \DominionEnterprises\Util\Http class.
 */

namespace DominionEnterprises\Util;
use DominionEnterprises\Util;

/**
 * Static class with various HTTP related functions.
 */
final class Http
{
    /**
     * Parses HTTP headers into an associative array.
     *
     * Example:
     * <code>
     * $headers = "HTTP/1.1 200 OK\r\n".
     *            "content-type: text/html; charset=UTF-8\r\n".
     *            "Server: Funky/1.0\r\n".
     *            "Set-Cookie: foo=bar\r\n".
     *            "Set-Cookie: baz=quux\r\n".
     *            "Folds: are\r\n\treformatted\r\n";
     * print_r(\DominionEnterprises\HttpUtil::parseHeaders($headers));
     * </code>
     * The above example will output:
     * <pre>
     * Array
     * (
     *     [Response Code] => 200
     *     [Response Status] => OK
     *     [Content-Type] => text/html; charset=UTF-8
     *     [Server] => Funky/1.0
     *     [Set-Cookie] => Array
     *     (
     *       [0] => foo=bar
     *       [1] => baz=quux
     *     )
     *     [Folds] => are reformatted
     * )
     * </pre>
     *
     * @param string $rawHeaders string containing HTTP headers
     *
     * @return array the parsed headers
     *
     * @throws \Exception Thrown if unable to parse the headers
     */
    public static function parseHeaders($rawHeaders)
    {
        Util::throwIfNotType(['string' => $rawHeaders], true);

        set_error_handler('\DominionEnterprises\Util::raiseException');
        try {
            $headers = [];
            $rawHeaders = preg_replace("/\r\n[\t ]+/", ' ', trim($rawHeaders));
            $fields = explode("\r\n", $rawHeaders);
            foreach ($fields as $field) {
                $match = null;
                if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
                    $key = $match[1];
                    // convert 'some-header' to 'Some-Header'
                    $key = strtolower(trim($key));
                    $key = ucwords(preg_replace('/[\s-]/', ' ', $key));
                    $key = strtr($key, ' ', '-');

                    $value = trim($match[2]);

                    if (!array_key_exists($key, $headers)) {
                        $headers[$key] = $value;
                        continue;
                    }

                    if (!is_array($headers[$key])) {
                        $headers[$key] = [$headers[$key]];
                    }

                    $headers[$key][] = $value;
                } elseif (preg_match('#([A-Za-z]+) +([^ ]+) +HTTP/([\d.]+)#', $field, $match)) {
                    $headers['Request Method'] = trim($match[1]);
                    $headers['Request Url'] = trim($match[2]);
                } elseif (preg_match('#HTTP/([\d.]+) +(\d{3}) +(.*)#', $field, $match)) {
                    $headers['Response Code'] = (int)$match[2];
                    $headers['Response Status'] = trim($match[3]);
                } else {
                    throw new \Exception("Unsupported header format: {$field}");
                }
            }

            restore_error_handler();
            return $headers;
        } catch (\Exception $e) {
            restore_error_handler();
            throw new \Exception('Unable to parse headers', 0, $e);
        }
    }

    /**
     * Generate URL-encoded query string
     *
     * Example:
     * <code>
     * $parameters = [
     *   'param1' => ['value', 'another value'],
     *   'param2' => 'a value',
     *   'param3' => false,
     * ];
     *
     * $queryString = \DominionEnterprises\HttpUtil::buildQueryString($parameters);
     *
     * echo $queryString
     * </code>
     *
     * Output:
     * <pre>
     * param1=value&param1=another+value&param2=a+value&param3=false
     * </pre>
     *
     * @param array $parameters An associative array containing parameter key/value(s)
     *
     * @return string the built query string
     */
    public static function buildQueryString(array $parameters)
    {
        $queryStrings = [];
        foreach ($parameters as $parameterName => $parameterValue) {
            $parameterName = urlencode($parameterName);

            if (is_array($parameterValue)) {
                foreach ($parameterValue as $eachValue) {
                    $eachValue = urlencode($eachValue);
                    $queryStrings[] = "{$parameterName}={$eachValue}";
                }
            } elseif ($parameterValue === false) {
                $queryStrings[] = "{$parameterName}=false";
            } elseif ($parameterValue === true) {
                $queryStrings[] = "{$parameterName}=true";
            } else {
                $parameterValue = urlencode($parameterValue);
                $queryStrings[] = "{$parameterName}={$parameterValue}";
            }
        }

        return implode('&', $queryStrings);
    }

    /**
     * Get an array of all url parameters.
     *
     * @param string $url The url to parse such as http://foo.com/bar/?id=boo&another=wee&another=boo
     * @param array $collapsedParams Parameters to collapse. ex. 'id' => ['boo'] to just 'id' => 'boo'. Exception thrown if more than 1 value
     *
     * @return array such as ['id' => ['boo'], 'another' => ['wee', 'boo']]
     *
     * @throws \InvalidArgumentException if $url was not a string
     * @throws \Exception if more than one value in a $collapsedParams param
     */
    public static function getQueryParams($url, array $collapsedParams = [])
    {
        if (!is_string($url)) {
            throw new \InvalidArgumentException('$url was not a string');
        }

        $queryString = parse_url($url, PHP_URL_QUERY);
        if (!is_string($queryString)) {
            return [];
        }

        $collapsedParams = array_flip($collapsedParams);

        $result = [];
        foreach (explode('&', $queryString) as $arg) {
            $name = $arg;
            $value = '';
            $nameAndValue = explode('=', $arg);
            if (isset($nameAndValue[1])) {
                list($name, $value) = $nameAndValue;
            }

            $name = urldecode($name);
            $value = urldecode($value);
            $collapsed = isset($collapsedParams[$name]);

            if (!array_key_exists($name, $result)) {
                if ($collapsed) {
                    $result[$name] = $value;
                    continue;
                }

                $result[$name] = [];
            }

            if ($collapsed) {
                throw new \Exception("Parameter '{$name}' had more than one value but in \$collapsedParams");
            }

            $result[$name][] = $value;
        }

        return $result;
    }

    /**
     * Get an array of all url parameters.
     *
     * @param string $url The url to parse such as http://foo.com/bar/?single=boo&multi=wee&multi=boo
     * @param array $expectedArrayParams List of parameter names which are not collapsed.
     *
     * @return array such as ['single' => 'boo', 'multi' => ['wee', 'boo']] if 'multi' is given in $expectedArrayParams
     *
     * @throws \InvalidArgumentException if $url was not a string
     * @throws \Exception if a parameter is given as array but not included in the expected array argument
     */
    public static function getQueryParamsCollapsed($url, array $expectedArrayParams = [])
    {
        if (!is_string($url)) {
            throw new \InvalidArgumentException('$url was not a string');
        }

        $queryString = parse_url($url, PHP_URL_QUERY);
        if (!is_string($queryString)) {
            return [];
        }

        $result = [];
        foreach (explode('&', $queryString) as $arg) {
            $name = $arg;
            $value = '';
            $nameAndValue = explode('=', $arg);
            if (isset($nameAndValue[1])) {
                list($name, $value) = $nameAndValue;
            }

            $name = urldecode($name);
            $value = urldecode($value);

            if (!array_key_exists($name, $result)) {
                $result[$name] = $value;
                continue;
            }

            if (!in_array($name, $expectedArrayParams)) {
                throw new \Exception("Parameter '{$name}' is not expected to be an array, but array given");
            }

            if (!is_array($result[$name])) {
                $result[$name] = [$result[$name]];
            }

            $result[$name][] = $value;
        }

        return $result;
    }
}
