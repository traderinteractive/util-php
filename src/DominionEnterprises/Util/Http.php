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
        Util::throwIfNotType(array('string' => $rawHeaders), true);

        set_error_handler('\DominionEnterprises\Util::raiseException');
        try {
            $headers = array();
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
                        $headers[$key] = array($headers[$key]);
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
     * $parameters = array(
     *   'param1' => array('value', 'another value'),
     *   'param2' => 'a value',
     * );
     *
     * $queryString = \DominionEnterprises\HttpUtil::buildQueryString($parameters);
     *
     * echo $queryString
     * </code>
     *
     * Output:
     * <pre>
     * param1=value&param1=another+value&param2=a+value
     * </pre>
     *
     * @param array $parameters An associative array containing parameter key/value(s)
     *
     * @return string the built query string
     */
    public static function buildQueryString(array $parameters)
    {
        $queryStrings = array();
        foreach ($parameters as $parameterName => $parameterValue) {
            if (is_array($parameterValue)) {
                foreach ($parameterValue as $eachValue) {
                    $eachValue = urlencode($eachValue);
                    $queryStrings[] = "{$parameterName}={$eachValue}";
                }
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
     * @param bool $collapse Flag to collapse single value array parameters
     * @param array $expectedArrayParams List of parameter names which are allowed to be repeated
     *
     * @return array such as ['id' => ['boo'], 'another' => ['wee', 'boo']]
     *
     * @throws \InvalidArgumentException if $url was not a string
     * @throws \InvalidArgumentException if $collapse was not a bool
     * @throws \Exception if a parameter is given as array but not included in the expected array argument
     */
    public static function getQueryParams($url, $collapse = false, array $expectedArrayParams = array())
    {
        if (!is_string($url)) {
            throw new \InvalidArgumentException('$url was not a string');
        }

        if ($collapse !== true && $collapse !== false) {
            throw new \InvalidArgumentException('$collapse was not a bool');
        }

        $queryString = parse_url($url, PHP_URL_QUERY);
        if (!is_string($queryString)) {
            return array();
        }

        $result = array();
        foreach (explode('&', $queryString) as $arg) {
            $name = null;
            $value = null;
            if (strpos($arg, '=') !== false) {
                list($name, $value) = explode('=', $arg);
            } else {
                $name = $arg;
                $value = '';
            }

            $name = urldecode($name);
            $value = urldecode($value);

            if (!$collapse) {
                if (!array_key_exists($name, $result)) {
                    $result[$name] = array();
                }

                $result[$name][] = $value;
                continue;
            }

            if (!array_key_exists($name, $result)) {
                $result[$name] = $value;
                continue;
            }

            if (!in_array($name, $expectedArrayParams)) {
                throw new \Exception("Parameter '{$name}' is not expected to be an array, but array given");
            }

            if (!is_array($result[$name])) {
                $result[$name] = array($result[$name]);
            }

            $result[$name][] = $value;
        }

        return $result;
    }
}
