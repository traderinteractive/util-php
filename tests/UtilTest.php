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
    public function raiseException_throwsErrorException()
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
    public function raiseException_setsExceptionPropertiesCorrectly()
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
    public function raiseException_returnsFalseIfErrorReportingDisabled()
    {
        $restoreLevel = error_reporting(0);
        $this->assertFalse(U::raiseException(E_USER_NOTICE, 'test', __FILE__, __LINE__));
        error_reporting($restoreLevel);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     */
    public function throwIfNotType_basicSuccess()
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
    public function throwIfNotType_stringFailure()
    {
        U::throwIfNotType(['string' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_boolFailure()
    {
        U::throwIfNotType(['bool' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_nullFailure()
    {
        U::throwIfNotType(['null' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_intFailure()
    {
        U::throwIfNotType(['int' => [1, 'not an int']]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_notStringTypeArg()
    {
        U::throwIfNotType([1]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_notBoolAllowNullsArg()
    {
        U::throwIfNotType([], false, 'BAD');
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_notBoolFailOnWhitespaceArg()
    {
        U::throwIfNotType([], 'BAD');
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_badFunctionName()
    {
        U::throwIfNotType(['FUNCTHATDOESNTEXIST' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     */
    public function throwIfNotType_allowNullsSuccess()
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
    public function throwIfNotType_whitespaceFailure()
    {
        U::throwIfNotType(['int' => 1, 'string' => '   '], true);
    }

    /**
     * @test
     * @covers ::ensureNot
     */
    public function ensureNot_success()
    {
        $this->assertTrue(U::ensureNot(false, is_string('boo')));
    }

    /**
     * @test
     * @covers ::ensureNot
     * @covers ::throwProvidedException
     * @expectedException \InvalidArgumentException
     */
    public function ensureNot_badArg()
    {
        U::ensureNot(false, false, 1);
    }

    /**
     * @test
     * @covers ::ensureNot
     * @covers ::throwProvidedException
     * @expectedException \Exception
     */
    public function ensureNot_baseException()
    {
        U::ensureNot(false, is_string(1));
    }

    /**
     * @test
     * @covers ::ensureNot
     * @covers ::throwProvidedException
     * @expectedException \Exception
     * @expectedExceptionMessage bah
     */
    public function ensureNot_userMessage()
    {
        U::ensureNot(false, is_string(1), 'bah');
    }

    /**
     * @test
     * @covers ::ensureNot
     * @covers ::throwProvidedException
     * @expectedException \Exception
     * @expectedExceptionMessage bah
     */
    public function ensureNot_dynamicException()
    {
        U::ensureNot(false, is_string(1), 'Exception', ['bah']);
    }

    /**
     * @test
     * @covers ::ensureNot
     * @covers ::throwProvidedException
     * @uses \DominionEnterprises\HttpException
     * @expectedException \DominionEnterprises\HttpException
     * @expectedExceptionMessage bah
     * @expectedExceptionCode 404
     */
    public function ensureNot_dynamicExceptionWithAlias()
    {
        U::ensureNot(false, is_string(1), 'http', ['bah', 404, 404]);
    }

    /**
     * @test
     * @covers ::ensureNot
     * @covers ::throwProvidedException
     * @expectedException \Exception
     * @expectedExceptionMessage foo
     * @expectedExceptionCode 2
     */
    public function ensureNot_exception()
    {
        U::ensureNot(false, false, new \Exception('foo', 2));
    }

    /**
     * @test
     * @covers ::ensure
     */
    public function ensure_success()
    {
        $this->assertTrue(U::ensure(true, is_string('boo')));
    }

    /**
     * @test
     * @covers ::ensure
     * @covers ::throwProvidedException
     * @expectedException \InvalidArgumentException
     */
    public function ensure_badArg()
    {
        U::ensure(false, true, 1);
    }

    /**
     * @test
     * @covers ::ensure
     * @covers ::throwProvidedException
     * @expectedException \Exception
     */
    public function ensure_baseException()
    {
        U::ensure(true, is_string(1));
    }

    /**
     * @test
     * @covers ::ensure
     * @covers ::throwProvidedException
     * @expectedException \Exception
     * @expectedExceptionMessage bah
     */
    public function ensure_userMessage()
    {
        U::ensure(true, is_string(1), 'bah');
    }

    /**
     * @test
     * @covers ::ensure
     * @covers ::throwProvidedException
     * @expectedException \Exception
     * @expectedExceptionMessage bah
     */
    public function ensure_dynamicException()
    {
        U::ensure(true, is_string(1), 'Exception', ['bah']);
    }

    /**
     * @test
     * @covers ::ensure
     * @covers ::throwProvidedException
     * @uses \DominionEnterprises\HttpException
     * @expectedException \DominionEnterprises\HttpException
     * @expectedExceptionMessage bah
     * @expectedExceptionCode 404
     */
    public function ensure_dynamicExceptionWithAlias()
    {
        U::ensure(true, is_string(1), 'http', ['bah', 404, 404]);
    }

    /**
     * @test
     * @covers ::ensure
     * @covers ::throwProvidedException
     * @expectedException \Exception
     * @expectedExceptionMessage foo
     * @expectedExceptionCode 2
     */
    public function ensure_exception()
    {
        U::ensure(true, false, new \Exception('foo', 2));
    }

    /**
     * @test
     * @covers ::throwProvidedException
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $exception is of an invalid type
     * @expectedExceptionCode 0
     */
    public function throwProvidedException_invalid()
    {
        U::throwProvidedException('\RidiculousException', [2]);
    }

    /**
     * @test
     * @covers ::throwProvidedException
     * @expectedException \Exception
     * @expectedExceptionMessage This is an exception
     * @expectedExceptionCode 0
     */
    public function throwProvidedException_nullException()
    {
        U::throwProvidedException(null, [2], 'This is an exception');
    }

    /**
     * @test
     * @covers ::setExceptionAliases
     * @covers ::getExceptionAliases
     */
    public function setExceptionAliases_getSet()
    {
        $exceptionAliases = ['shortNameOne' => 'fullNameOne', 'shortNameTwo' => 'fullNameTwo'];
        U::setExceptionAliases($exceptionAliases);
        $this->assertSame($exceptionAliases, U::getExceptionAliases());
    }

    /**
     * @test
     * @covers ::callStatic
     */
    public function callStatic_private()
    {
        $this->assertSame('testPrivateBoo', U::callStatic('\DominionEnterprises\CallStaticTest::testPrivate', ['Boo']));
    }

    /**
     * @test
     * @covers ::callStatic
     */
    public function callStatic_protected()
    {
        $this->assertSame('testProtectedBoo', U::callStatic('\DominionEnterprises\CallStaticTest::testProtected', ['Boo']));
    }

    /**
     * @test
     * @covers ::callStatic
     */
    public function callStatic_public()
    {
        $this->assertSame('testPublicBoo', U::callStatic('\DominionEnterprises\CallStaticTest::testPublic', ['Boo']));
    }

    /**
     * @test
     * @covers ::callStatic
     */
    public function callStatic_callable()
    {
        $this->assertSame('testPrivateBoo', U::callStatic(['\DominionEnterprises\CallStaticTest', 'testPrivate'], ['Boo']));
    }

    /**
     * @test
     * @covers ::callStatic
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $method was not a callable
     */
    public function callStatic_notStringMethod()
    {
        U::callStatic(true);
    }

    /**
     * @test
     * @covers ::callStatic
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $method was not a callable
     */
    public function callStatic_notCallableArrayMethod()
    {
        U::callStatic(['\DominionEnterprises\CallStaticTest', 2]);
    }

    /**
     * @test
     * @covers ::callStatic
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $method was not static
     */
    public function callStatic_notStaticMethod()
    {
        U::callStatic('\DominionEnterprises\CallStaticTest::notStatic');
    }
}

final class CallStaticTest
{
    private static function testPrivate($arg)
    {
        return 'testPrivate' . $arg;
    }

    protected static function testProtected($arg)
    {
        return 'testProtected' . $arg;
    }

    public static function testPublic($arg)
    {
        return 'testPublic' . $arg;
    }

    private function notStatic()
    {
    }
}
