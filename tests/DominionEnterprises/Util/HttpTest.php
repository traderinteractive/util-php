<?php
/**
 * Defines the \DominionEnterprises\Util\HttpTest class
 */

namespace DominionEnterprises\Util;
use DominionEnterprises\Util\Http as H;

/**
 * Defines unit tests for the \DominionEnterprises\Util\Http class
 */
final class HttpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @group unit
     * @covers \DominionEnterprises\Util\Http::parseHeaders
     */
    public function parseHeaders_basicUsage()
    {
        $headers = 'Content-Type: text/json';
        $result = H::parseHeaders($headers);
        $this->assertSame(array('Content-Type' => 'text/json'), $result);
    }

    /**
     * @test
     * @group unit
     * @covers \DominionEnterprises\Util\Http::parseHeaders
     */
    public function parseHeaders_malformed()
    {
        try {
            $headers = "&some\r\nbad+headers";
            $result = H::parseHeaders($headers);
            $this->fail('No exception thrown');
        } catch (\Exception $e) {
            $this->assertSame('Unable to parse headers', $e->getMessage());
        }
    }

    /**
     * Verifies parseHeaders retains the functionality of http_parse_headers()
     *
     * @test
     * @group unit
     * @covers \DominionEnterprises\Util\Http::parseHeaders
     */
    public function parseHeaders_peclHttpFunctionality()
    {
        $headers = <<<EOT
HTTP/1.1 200 OK\r\n
content-type: text/html; charset=UTF-8\r\n
Server: Funky/1.0\r\n
Set-Cookie: foo=bar\r\n
Set-Cookie: baz=quux\r\n
Set-Cookie: key=value\r\n
EOT;
        $expected = array(
            'Response Code' => 200,
            'Response Status' => 'OK',
            'Content-Type' => 'text/html; charset=UTF-8',
            'Server' => 'Funky/1.0',
            'Set-Cookie' => array('foo=bar', 'baz=quux', 'key=value'),
        );
        $result = H::parseHeaders($headers);
        $this->assertSame($expected, $result);
    }

    /**
     * Verifies Request Method and Request Url are set properly
     *
     * @test
     * @group unit
     * @covers \DominionEnterprises\Util\Http::parseHeaders
     */
    public function parseHeaders_methodAndUrlSet()
    {
        $headers = <<<EOT
GET /file.xml HTTP/1.1\r\n
Host: www.example.com\r\n
Accept: */*\r\n
EOT;
        $expected = array(
            'Request Method' => 'GET',
            'Request Url' => '/file.xml',
            'Host' => 'www.example.com',
            'Accept' => '*/*',
        );
        $result = H::parseHeaders($headers);
        $this->assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function buildQueryString_basicUse()
    {
        $data = array(
            'foo' => 'bar',
            'baz' => 'boom',
            'cow' => 'milk',
            'php' => 'hypertext processor',
        );

        $this->assertSame('foo=bar&baz=boom&cow=milk&php=hypertext+processor', H::buildQueryString($data));
    }

    /**
     * @test
     */
    public function buildQueryString_multiValue()
    {
        $data = array(
            'param1' => array('value', 'another value'),
            'param2' => 'a value',
        );

        $this->assertSame('param1=value&param1=another+value&param2=a+value', H::buildQueryString($data));
    }

    /**
     * @test
     */
    public function buildQueryString_complexValues()
    {
        $this->assertSame('abc=1%242%283&abc=4%295%2A6', H::buildQueryString(array('abc' => array('1$2(3', '4)5*6'))));
    }

    /**
     * Verifies Mulit Parameter Method can handle a normal url
     *
     * @test
     * @group unit
     * @covers \DominionEnterprises\Util\Http::getQueryParams
     */
    public function getQueryParams_normal()
    {
        $url = 'http://foo.com/bar/?otherStuff=green&stuff=yeah&moreStuff=rock&moreStuff=jazz&otherStuff=blue&otherStuff=black';
        $expected = array(
            'otherStuff' => array(
                'green',
                'blue',
                'black',
            ),
            'stuff' => array('yeah'),
            'moreStuff' => array(
                'rock',
                'jazz',
            ),
        );
        $result = H::getQueryParams($url);
        $this->assertSame($expected, $result);
    }

    /**
     * Verifies Mulit Parameter Method can handle a url with an empty parameter
     *
     * @test
     * @group unit
     * @covers \DominionEnterprises\Util\Http::getQueryParams
     */
    public function getQueryParams_emptyParameter()
    {
        $url = 'http://foo.com/bar/?stuff=yeah&moreStuff=&moreStuff=jazz&otherStuff';
        $expected = array(
            'stuff' => array('yeah'),
            'moreStuff' => array(
                '',
                'jazz',
            ),
            'otherStuff' => array(''),
        );
        $result = H::getQueryParams($url);
        $this->assertSame($expected, $result);
    }

    /**
     * Verifies multi parameter method with a garbage query string
     *
     * @test
     * @group unit
     * @covers \DominionEnterprises\Util\Http::getQueryParams
     */
    public function getQueryParams_garbage()
    {
        $this->assertSame(array(), H::getQueryParams('GARBAGE'));
    }

    /**
     * @test
     * @group unit
     * @covers \DominionEnterprises\Util\Http::getQueryParams
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $url was not a string
     */
    public function getQueryParams_urlNotString()
    {
        H::getQueryParams(1);
    }
}
