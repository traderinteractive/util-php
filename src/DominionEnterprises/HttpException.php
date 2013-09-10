<?php
/**
 * Defines the \DominionEnterprises\HttpException class.
 */

namespace DominionEnterprises;

/**
 * Exception to throw when an http status code should be included.
 *
 * Defaults to a 500 error.
 */
final class HttpException extends \Exception
{
    private $_httpStatusCode;
    private $_userMessage;

    /**
     * Constructs
     *
     * @param string $message @see \Exception::__construct()
     * @param int $httpStatusCode a valid http status code
     * @param int $code @see \Exception::__construct()
     * @param \Exception $previous @see \Exception::__construct()
     * @param string|null $userMessage a nicer message to display to the user sans sensitive details
     *
     * @throws \InvalidArgumentException if $message is not a string
     * @throws \InvalidArgumentException if $httpStatusCode is not an int
     * @throws \InvalidArgumentException if $code is not an int
     * @throws \InvalidArgumentException if $userMmessage is not null and is not a string
     */
    public function __construct(
        $message = 'Application Error',
        $httpStatusCode = 500,
        $code = 0,
        \Exception $previous = null,
        $userMessage = null
    )
    {
        Util::throwIfNotType(array('string' => array($message), 'int' => array($httpStatusCode, $code)));
        Util::throwIfNotType(array('string' => array($userMessage)), false, true);

        parent::__construct($message, $code, $previous);

        $this->_httpStatusCode = $httpStatusCode;

        if ($userMessage !== null) {
            $this->_userMessage = $userMessage;
        } else {
            $this->_userMessage = $message;
        }
    }

    /**
     * Getter for $httpStatusCode
     *
     * @return int the http status code
     */
    public function getHttpStatusCode()
    {
        return $this->_httpStatusCode;
    }

    /**
     * Getter for $userMessage
     *
     * @return string the user message
     */
    public function getUserMessage()
    {
        return $this->_userMessage;
    }
}
