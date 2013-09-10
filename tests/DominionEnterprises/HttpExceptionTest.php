<?php
/**
 * Defines the HttpExceptionTest class
 */

namespace DominionEnterprises\Tests;
use DominionEnterprises\HttpException;

/**
 * Unit tests for the \DominionEnterprises\Exception class
 */
final class HttpExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function nonStringMessage()
    {
        new HttpException(1);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function nonIntHttpCode()
    {
        new HttpException('message', 1.1);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function nonIntCode()
    {
        new HttpException('message', 1, 1.1);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function nonStringUserMessage()
    {
        new HttpException('message', 1, 1, null, 1);
    }

    /**
     * @test
     */
    public function userMessage()
    {
        $eWithNull = new HttpException('message', 1, 1, null, null);
        $eWithUserMessage = new HttpException('message', 1, 1, null, 'a user message');

        $this->assertSame('message', $eWithNull->getUserMessage());
        $this->assertSame('a user message', $eWithUserMessage->getUserMessage());
    }

    /**
     * @test
     */
    public function httpCode()
    {
        $e = new HttpException('message', 1);
        $this->assertSame(1, $e->getHttpStatusCode());
    }
}
