<?php
/**
 * Defines the UtilTest class
 */

namespace DominionEnterprises;

use DominionEnterprises\Util as U;

/**
 * @coversDefaultClass \DominionEnterprises\Util
 */
final class UtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers ::getExceptionInfo
     */
    public function getExceptionInfo()
    {
        $expectedLine = __LINE__ + 1;
        $result = U::getExceptionInfo(new \Exception('a message', 42));

        $this->assertTrue(strpos($result['trace'], 'getExceptionInfo') !== false);

        $expected = [
            'type' => 'Exception',
            'message' => 'a message',
            'code' => 42,
            'file' => __FILE__,
            'line' => $expectedLine,
            'trace' => $result['trace'],
        ];
        $this->assertSame($expected, $result);
    }

    /**
     * @test
     * @covers ::raiseException
     * @expectedException \ErrorException
     * @covers \DominionEnterprises\Util::raiseException
     */
    public function raiseExceptionThrowsErrorException()
    {
        set_error_handler('\DominionEnterprises\Util::raiseException');
        trigger_error('test');
        restore_error_handler();
    }

    /**
     * @test
     * @covers ::raiseException
     * @covers \DominionEnterprises\Util::raiseException
     */
    public function raiseExceptionSetsExceptionPropertiesCorrectly()
    {
        set_error_handler('\DominionEnterprises\Util::raiseException');
        try {
            trigger_error('test', E_USER_NOTICE);
        } catch (\ErrorException $e) {
            $this->assertSame('test', $e->getMessage());
            $this->assertSame(0, $e->getCode());
            $this->assertSame(E_USER_NOTICE, $e->getSeverity());
            $this->assertSame((__LINE__) - 5, $e->getLine());
            $this->assertSame(__FILE__, $e->getFile());
        }

        restore_error_handler();
    }

    /**
     * @test
     * @covers ::raiseException
     */
    public function raiseExceptionReturnsFalseIfErrorReportingDisabled()
    {
        $restoreLevel = error_reporting(0);
        $this->assertFalse(U::raiseException(E_USER_NOTICE, 'test', __FILE__, __LINE__));
        error_reporting($restoreLevel);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     */
    public function throwIfNotTypeBasicSuccess()
    {
        U::throwIfNotType(['string' => ['string1', 'string2'], 'integer' => [1, 2], 'int' => 3, 'null' => null]);
        //Added for strict tests. throwIfNotType() throws on failure
        $this->assertTrue(true);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotTypeStringFailure()
    {
        U::throwIfNotType(['string' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotTypeBoolFailure()
    {
        U::throwIfNotType(['bool' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotTypeNullFailure()
    {
        U::throwIfNotType(['null' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotTypeIntFailure()
    {
        U::throwIfNotType(['int' => [1, 'not an int']]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotTypeNotStringTypeArg()
    {
        U::throwIfNotType([1]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotTypeNotBoolAllowNullsArg()
    {
        U::throwIfNotType([], false, 'BAD');
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotTypeNotBoolFailOnWhitespaceArg()
    {
        U::throwIfNotType([], 'BAD');
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotTypeBadFunctionName()
    {
        U::throwIfNotType(['FUNCTHATDOESNTEXIST' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     */
    public function throwIfNotTypeAllowNullsSuccess()
    {
        U::throwIfNotType(['int' => [1, null], 'string' => null, 'bool' => null], false, true);
        //Added for strict tests. throwIfNotType() throws on failure
        $this->assertTrue(true);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotTypeWhitespaceFailure()
    {
        U::throwIfNotType(['int' => 1, 'string' => '   '], true);
    }

    /**
     * @test
     * @covers ::ensureNot
     */
    public function ensureNotSuccess()
    {
        $this->assertTrue(U::ensureNot(false, is_string('boo')));
    }

    /**
     * @test
     * @covers ::ensureNot
     * @expectedException \InvalidArgumentException
     */
    public function ensureNotBadArg()
    {
        U::ensureNot(false, false, 1);
    }

    /**
     * @test
     * @covers ::ensureNot
     * @expectedException \Exception
     */
    public function ensureNotBaseException()
    {
        U::ensureNot(false, is_string(1));
    }

    /**
     * @test
     * @covers ::ensureNot
     * @expectedException \Exception
     * @expectedExceptionMessage bah
     */
    public function ensureNotUserMessage()
    {
        U::ensureNot(false, is_string(1), 'bah');
    }

    /**
     * @test
     * @covers ::ensureNot
     * @expectedException \Exception
     * @expectedExceptionMessage bah
     */
    public function ensureNotDynamicException()
    {
        U::ensureNot(false, is_string(1), 'Exception', ['bah']);
    }

    /**
     * @test
     * @covers ::ensureNot
     * @uses \DominionEnterprises\HttpException
     * @expectedException \DominionEnterprises\HttpException
     * @expectedExceptionMessage bah
     * @expectedExceptionCode 404
     */
    public function ensureNotDynamicExceptionWithAlias()
    {
        U::ensureNot(false, is_string(1), 'http', ['bah', 404, 404]);
    }

    /**
     * @test
     * @covers ::ensureNot
     * @expectedException \Exception
     * @expectedExceptionMessage foo
     * @expectedExceptionCode 2
     */
    public function ensureNotException()
    {
        U::ensureNot(false, false, new \Exception('foo', 2));
    }

    /**
     * @test
     * @covers ::ensure
     */
    public function ensureSuccess()
    {
        $this->assertTrue(U::ensure(true, is_string('boo')));
    }

    /**
     * @test
     * @covers ::ensure
     * @expectedException \InvalidArgumentException
     */
    public function ensureBadArg()
    {
        U::ensure(false, true, 1);
    }

    /**
     * @test
     * @covers ::ensure
     * @expectedException \Exception
     */
    public function ensureBaseException()
    {
        U::ensure(true, is_string(1));
    }

    /**
     * @test
     * @covers ::ensure
     * @expectedException \Exception
     * @expectedExceptionMessage bah
     */
    public function ensureUserMessage()
    {
        U::ensure(true, is_string(1), 'bah');
    }

    /**
     * @test
     * @covers ::ensure
     * @expectedException \Exception
     * @expectedExceptionMessage bah
     */
    public function ensureDynamicException()
    {
        U::ensure(true, is_string(1), 'Exception', ['bah']);
    }

    /**
     * @test
     * @covers ::ensure
     * @uses \DominionEnterprises\HttpException
     * @expectedException \DominionEnterprises\HttpException
     * @expectedExceptionMessage bah
     * @expectedExceptionCode 404
     */
    public function ensureDynamicExceptionWithAlias()
    {
        U::ensure(true, is_string(1), 'http', ['bah', 404, 404]);
    }

    /**
     * @test
     * @covers ::ensure
     * @expectedException \Exception
     * @expectedExceptionMessage foo
     * @expectedExceptionCode 2
     */
    public function ensureException()
    {
        U::ensure(true, false, new \Exception('foo', 2));
    }

    /**
     * @test
     * @covers ::setExceptionAliases
     * @covers ::getExceptionAliases
     */
    public function setExceptionAliasesGetSet()
    {
        $exceptionAliases = ['shortNameOne' => 'fullNameOne', 'shortNameTwo' => 'fullNameTwo'];
        U::setExceptionAliases($exceptionAliases);
        $this->assertSame($exceptionAliases, U::getExceptionAliases());
    }

    /**
     * @test
     * @covers ::callStatic
     */
    public function callStaticPrivate()
    {
        $this->assertSame('privateTestBoo', U::callStatic(__CLASS__ . '::privateTest', ['Boo']));
    }

    /**
     * @test
     * @covers ::callStatic
     */
    public function callStaticProtected()
    {
        $this->assertSame('protectedTestBoo', U::callStatic(__CLASS__ . '::protectedTest', ['Boo']));
    }

    /**
     * @test
     * @covers ::callStatic
     */
    public function callStaticPublic()
    {
        $this->assertSame('publicTestBoo', U::callStatic(__CLASS__ . '::publicTest', ['Boo']));
    }

    /**
     * @test
     * @covers ::callStatic
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $method was not a string
     */
    public function callStaticNotStringMethod()
    {
        U::callStatic(true);
    }

    /**
     * @test
     * @covers ::callStatic
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $method was not static
     */
    public function callStaticNotStaticMethod()
    {
        U::callStatic(__CLASS__ . '::notStatic');
    }

    private static function privateTest($arg)
    {
        return 'privateTest' . $arg;
    }

    protected static function protectedTest($arg)
    {
        return 'protectedTest' . $arg;
    }

    public static function publicTest($arg)
    {
        return 'publicTest' . $arg;
    }

    private function notStatic()
    {
    }
}
