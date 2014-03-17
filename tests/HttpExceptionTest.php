<?php
/**
 * Defines the HttpExceptionTest class
 */

namespace DominionEnterprises;

/**
 * @coversDefaultClass \DominionEnterprises\HttpException
 */
final class HttpExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers ::__construct
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $message was not a string
     */
    public function nonStringMessage()
    {
        new HttpException(1);
    }

    /**
     * @test
     * @covers ::__construct
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $httpStatusCode was not an int
     */
    public function nonIntHttpCode()
    {
        new HttpException('message', 1.1);
    }

    /**
     * @test
     * @covers ::__construct
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $code was not an int
     */
    public function nonIntCode()
    {
        new HttpException('message', 1, 1.1);
    }

    /**
     * @test
     * @covers ::__construct
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $userMessage was not null and not a string
     */
    public function nonStringUserMessage()
    {
        new HttpException('message', 1, 1, null, 1);
    }

    /**
     * @test
     * @covers ::__construct()
     * @covers ::getUserMessage()
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
     * @covers ::__construct()
     * @covers ::getHttpStatusCode()
     */
    public function httpCode()
    {
        $e = new HttpException('message', 1);
        $this->assertSame(1, $e->getHttpStatusCode());
    }
}
