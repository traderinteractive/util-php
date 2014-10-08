<?php
/**
 * Defines the \DominionEnterprises\Util\HttpTest class
 */

namespace DominionEnterprises\Util;
use DominionEnterprises\Util\Http as H;

/**
 * Defines unit tests for the \DominionEnterprises\Util\Http class
 * @coversDefaultClass \DominionEnterprises\Util\Http
 */
final class HttpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @group unit
     * @covers ::parseHeaders
     * @uses \DominionEnterprises\Util::throwIfNotType
     * @uses \DominionEnterprises\Util::raiseException
     */
    public function parseHeaders_basicUsage()
    {
        $headers = 'Content-Type: text/json';
        $result = H::parseHeaders($headers);
        $this->assertSame(['Content-Type' => 'text/json'], $result);
    }

    /**
     * @test
     * @group unit
     * @covers ::parseHeaders
     * @uses \DominionEnterprises\Util::throwIfNotType
     * @uses \DominionEnterprises\Util::raiseException
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
     * @covers ::parseHeaders
     * @uses \DominionEnterprises\Util::throwIfNotType
     * @uses \DominionEnterprises\Util::raiseException
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
        $expected = [
            'Response Code' => 200,
            'Response Status' => 'OK',
            'Content-Type' => 'text/html; charset=UTF-8',
            'Server' => 'Funky/1.0',
            'Set-Cookie' => ['foo=bar', 'baz=quux', 'key=value'],
        ];
        $result = H::parseHeaders($headers);
        $this->assertSame($expected, $result);
    }

    /**
     * Verifies Request Method and Request Url are set properly
     *
     * @test
     * @group unit
     * @covers ::parseHeaders
     * @uses \DominionEnterprises\Util::throwIfNotType
     * @uses \DominionEnterprises\Util::raiseException
     */
    public function parseHeaders_methodAndUrlSet()
    {
        $headers = <<<EOT
GET /file.xml HTTP/1.1\r\n
Host: www.example.com\r\n
Accept: */*\r\n
EOT;
        $expected = ['Request Method' => 'GET', 'Request Url' => '/file.xml', 'Host' => 'www.example.com', 'Accept' => '*/*'];
        $result = H::parseHeaders($headers);
        $this->assertSame($expected, $result);
    }

    /**
     * @test
     * @covers ::buildQueryString
     */
    public function buildQueryString_basicUse()
    {
        $data = ['foo' => 'bar', 'baz' => 'boom', 'cow' => 'milk', 'php' => 'hypertext processor', 'theFalse' => false, 'theTrue' => true];

        $this->assertSame('foo=bar&baz=boom&cow=milk&php=hypertext%20processor&theFalse=false&theTrue=true', H::buildQueryString($data));
    }

    /**
     * @test
     * @covers ::buildQueryString
     */
    public function buildQueryString_multiValue()
    {
        $data = ['param1' => ['value', 'another value'], 'param2' => 'a value'];

        $this->assertSame('param1=value&param1=another%20value&param2=a%20value', H::buildQueryString($data));
    }

    /**
     * @test
     * @covers ::buildQueryString
     */
    public function buildQueryString_complexValues()
    {
        $this->assertSame('a%20b%20c=1%242%283&a%20b%20c=4%295%2A6', H::buildQueryString(['a b c' => ['1$2(3', '4)5*6']]));
    }

    /**
     * Verifies Mulit Parameter Method can handle a normal url
     *
     * @test
     * @group unit
     * @covers ::getQueryParams
     */
    public function getQueryParams_normal()
    {
        $url = 'http://foo.com/bar/?otherStuff=green&stuff=yeah&moreStuff=rock&moreStuff=jazz&otherStuff=blue&otherStuff=black';
        $expected = [
            'otherStuff' => ['green', 'blue', 'black'],
            'stuff' => ['yeah'],
            'moreStuff' => ['rock', 'jazz'],
        ];
        $result = H::getQueryParams($url);
        $this->assertSame($expected, $result);
    }

    /**
     * Verifies Mulit Parameter Method can handle a url with an empty parameter
     *
     * @test
     * @group unit
     * @covers ::getQueryParams
     */
    public function getQueryParams_emptyParameter()
    {
        $url = 'http://foo.com/bar/?stuff=yeah&moreStuff=&moreStuff=jazz&otherStuff';
        $expected = [
            'stuff' => ['yeah'],
            'moreStuff' => ['', 'jazz'],
            'otherStuff' => [''],
        ];
        $result = H::getQueryParams($url);
        $this->assertSame($expected, $result);
    }

    /**
     * Verifies multi parameter method with a garbage query string
     *
     * @test
     * @group unit
     * @covers ::getQueryParams
     */
    public function getQueryParams_garbage()
    {
        $this->assertSame([], H::getQueryParams('GARBAGE'));
    }

    /**
     * @test
     * @group unit
     * @covers ::getQueryParams
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $url was not a string
     */
    public function getQueryParams_urlNotString()
    {
        H::getQueryParams(1);
    }

    /**
     * @test
     * @covers ::getQueryParams
     */
    public function getQueryParams_collapsed()
    {
        $result = H::getQueryParams('http://foo.com/bar/?stuff=yeah&moreStuff=mhmm', ['stuff', 'notThere']);
        $this->assertSame(['stuff' => 'yeah', 'moreStuff' => ['mhmm']], $result);
    }

    /**
     * @test
     * @covers ::getQueryParams
     * @expectedException \Exception
     * @expectedExceptionMessage Parameter 'stuff' had more than one value but in $collapsedParams
     */
    public function getQueryParams_collapsedMoreThanOneValue()
    {
        H::getQueryParams('http://foo.com/bar/?stuff=yeah&stuff=boy&moreStuff=mhmm', ['stuff']);
    }

    /**
     * @test
     * @covers ::getQueryParamsCollapsed
     */
    public function getQueryParamsCollapsed()
    {
        $url = 'http://foo.com/bar/?boo=1&foo=bar&boo=2';
        $actual = H::getQueryParamsCollapsed($url, ['boo']);
        $this->assertSame(['boo' => ['1', '2'], 'foo' => 'bar'], $actual);
    }

    /**
     * @test
     * @covers ::getQueryParamsCollapsed
     * @expectedException \Exception
     * @expectedExceptionMessage Parameter 'boo' is not expected to be an array, but array given
     */
    public function getQueryParamsCollapsed_unexpectedArray()
    {
        $url = 'http://foo.com/bar/?boo=1&foo=bar&boo=2';
        H::getQueryParamsCollapsed($url);
    }

    /**
     * @test
     * @covers ::getQueryParamsCollapsed
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $url was not a string
     */
    public function getQueryParamsCollapsed_urlNotString()
    {
        H::getQueryParamsCollapsed(1);
    }

    /**
     * Verifies multi parameter method with a garbage query string
     *
     * @test
     * @covers ::getQueryParamsCollapsed
     */
    public function getQueryParamsCollasped_garbage()
    {
        $this->assertSame([], H::getQueryParamsCollapsed('GARBAGE'));
    }

    /**
     * Verifies Mulit Parameter Method can handle a url with an empty parameter
     *
     * @test
     * @covers ::getQueryParamsCollapsed
     */
    public function getQueryParamsCollapsed_emptyParameter()
    {
        $url = 'http://foo.com/bar/?stuff=yeah&moreStuff=&moreStuff=jazz&otherStuff';
        $expected = ['stuff' => 'yeah', 'moreStuff' => ['', 'jazz'], 'otherStuff' => ''];
        $this->assertSame($expected, H::getQueryParamsCollapsed($url, ['moreStuff']));
    }
}
