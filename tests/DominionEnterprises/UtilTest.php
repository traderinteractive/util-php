<?php
/**
 * Defines the UtilTest class
 */

namespace DominionEnterprises;
use DominionEnterprises\Util as U;

/**
 * Test class for \DominionEnterprises\Util
 */
final class UtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getExceptionInfo()
    {
        $expectedLine = __LINE__ + 1;
        $result = U::getExceptionInfo(new \Exception('a message', 42));

        $this->assertTrue(strpos($result['trace'], 'getExceptionInfo') !== false);

        $expected = array(
            'type' => 'Exception',
            'message' => 'a message',
            'code' => 42,
            'file' => __FILE__,
            'line' => $expectedLine,
            'trace' => $result['trace'],
        );
        $this->assertSame($expected, $result);
    }

    /**
     * @test
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
     * @covers \DominionEnterprises\Util::raiseException
     */
    public function raiseException_returnsFalseIfErrorReportingDisabled()
    {
        $restoreLevel = error_reporting(0);
        $this->assertFalse(U::raiseException(E_USER_NOTICE, 'test', __FILE__, __LINE__));
        error_reporting($restoreLevel);
    }

    /**
     * @test
     */
    public function throwIfNotType_basicSuccess()
    {
        U::throwIfNotType(array('string' => array('string1', 'string2'), 'integer' => array(1, 2), 'int' => 3, 'null' => null));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_stringFailure()
    {
        U::throwIfNotType(array('string' => 2));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_boolFailure()
    {
        U::throwIfNotType(array('bool' => 2));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_nullFailure()
    {
        U::throwIfNotType(array('null' => 2));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_intFailure()
    {
        U::throwIfNotType(array('int' => array(1, 'not an int')));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_notStringTypeArg()
    {
        U::throwIfNotType(array(1));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_notBoolAllowNullsArg()
    {
        U::throwIfNotType(array(), false, 'BAD');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_notBoolFailOnWhitespaceArg()
    {
        U::throwIfNotType(array(), 'BAD');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_badFunctionName()
    {
        U::throwIfNotType(array('FUNCTHATDOESNTEXIST' => 2));
    }

    /**
     * @test
     */
    public function throwIfNotType_allowNullsSuccess()
    {
        U::throwIfNotType(array('int' => array(1, null), 'string' => null, 'bool' => null), false, true);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwIfNotType_whitespaceFailure()
    {
        U::throwIfNotType(array('int' => 1, 'string' => '   '), true);
    }

    /**
     * @test
     */
    public function ensureNot_success()
    {
        $this->assertTrue(U::ensureNot(false, is_string('boo')));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function ensureNot_badArg()
    {
        U::ensureNot(false, false, 1);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function ensureNot_baseException()
    {
        U::ensureNot(false, is_string(1));
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage bah
     */
    public function ensureNot_userMessage()
    {
        U::ensureNot(false, is_string(1), 'bah');
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage bah
     */
    public function ensureNot_dynamicException()
    {
        U::ensureNot(false, is_string(1), 'Exception', array('bah'));
    }

    /**
     * @test
     * @expectedException \DominionEnterprises\HttpException
     * @expectedExceptionMessage bah
     * @expectedExceptionCode 404
     */
    public function ensureNot_dynamicExceptionWithAlias()
    {
        U::ensureNot(false, is_string(1), 'http', array('bah', 404, 404));
    }

    /**
     * @test
     */
    public function ensure_success()
    {
        $this->assertTrue(U::ensure(true, is_string('boo')));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function ensure_badArg()
    {
        U::ensure(false, true, 1);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function ensure_baseException()
    {
        U::ensure(true, is_string(1));
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage bah
     */
    public function ensure_userMessage()
    {
        U::ensure(true, is_string(1), 'bah');
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage bah
     */
    public function ensure_dynamicException()
    {
        U::ensure(true, is_string(1), 'Exception', array('bah'));
    }

    /**
     * @test
     * @expectedException \DominionEnterprises\HttpException
     * @expectedExceptionMessage bah
     * @expectedExceptionCode 404
     */
    public function ensure_dynamicExceptionWithAlias()
    {
        U::ensure(true, is_string(1), 'http', array('bah', 404, 404));
    }

    /**
     * @test
     */
    public function setExceptionAliases_getSet()
    {
        $exceptionAliases = array('shortNameOne' => 'fullNameOne', 'shortNameTwo' => 'fullNameTwo');
        U::setExceptionAliases($exceptionAliases);
        $this->assertSame($exceptionAliases, U::getExceptionAliases());
    }

    /**
     * @test
     */
    public function callStatic_private()
    {
        $this->assertSame('testPrivateBoo', U::callStatic('\DominionEnterprises\CallStaticTest::testPrivate', array('Boo')));
    }

    /**
     * @test
     */
    public function callStatic_protected()
    {
        $this->assertSame('testProtectedBoo', U::callStatic('\DominionEnterprises\CallStaticTest::testProtected', array('Boo')));
    }

    /**
     * @test
     */
    public function callStatic_public()
    {
        $this->assertSame('testPublicBoo', U::callStatic('\DominionEnterprises\CallStaticTest::testPublic', array('Boo')));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $method was not a string
     */
    public function callStatic_notStringMethod()
    {
        U::callStatic(true);
    }

    /**
     * @test
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
