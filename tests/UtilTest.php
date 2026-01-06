<?php

namespace TraderInteractive;

use Error;
use Throwable;
use TraderInteractive\Util as Utility;
use ErrorException;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * @coversDefaultClass \TraderInteractive\Util
 * @covers ::<private>
 */
final class UtilTest extends TestCase
{
    /**
     * @test
     * @covers ::getExceptionInfo
     */
    public function getExceptionInfo()
    {
        $expectedLine = __LINE__ + 1;
        $result = Utility::getExceptionInfo(new Exception('a message', 42));

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
     * @expectedException ErrorException
     */
    public function raiseExceptionThrowsErrorException()
    {
        set_error_handler('\TraderInteractive\Util::raiseException');
        trigger_error('test');
        restore_error_handler();
    }

    /**
     * @test
     * @covers ::raiseException
     */
    public function raiseExceptionSetsExceptionPropertiesCorrectly()
    {
        set_error_handler('\TraderInteractive\Util::raiseException');
        try {
            trigger_error('test', E_USER_NOTICE);
        } catch (Throwable $e) {
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
        $this->assertFalse(Utility::raiseException(E_USER_NOTICE, 'test', __FILE__, __LINE__));
        error_reporting($restoreLevel);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     */
    public function throwIfNotTypeBasicSuccess()
    {
        Utility::throwIfNotType(['string' => ['string1', 'string2'], 'integer' => [1, 2], 'int' => 3, 'null' => null]);
        //Added for strict tests. throwIfNotType() throws on failure
        $this->assertTrue(true);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException InvalidArgumentException
     */
    public function throwIfNotTypeStringFailure()
    {
        Utility::throwIfNotType(['string' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException InvalidArgumentException
     */
    public function throwIfNotTypeBoolFailure()
    {
        Utility::throwIfNotType(['bool' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException InvalidArgumentException
     */
    public function throwIfNotTypeNullFailure()
    {
        Utility::throwIfNotType(['null' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException InvalidArgumentException
     */
    public function throwIfNotTypeIntFailure()
    {
        Utility::throwIfNotType(['int' => [1, 'not an int']]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException InvalidArgumentException
     */
    public function throwIfNotTypeNotStringTypeArg()
    {
        Utility::throwIfNotType([1]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException InvalidArgumentException
     */
    public function throwIfNotTypeBadFunctionName()
    {
        Utility::throwIfNotType(['FUNCTHATDOESNTEXIST' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     */
    public function throwIfNotTypeAllowNullsSuccess()
    {
        Utility::throwIfNotType(['int' => [1, null], 'string' => null, 'bool' => null], false, true);
        //Added for strict tests. throwIfNotType() throws on failure
        $this->assertTrue(true);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     * @expectedException InvalidArgumentException
     */
    public function throwIfNotTypeWhitespaceFailure()
    {
        Utility::throwIfNotType(['int' => 1, 'string' => '   '], true);
    }

    /**
     * @test
     * @covers ::ensureNot
     */
    public function ensureNotSuccess()
    {
        $this->assertTrue(Utility::ensureNot(false, is_string('boo')));
    }

    /**
     * @test
     * @covers ::ensureNot
     * @expectedException InvalidArgumentException
     */
    public function ensureNotBadArg()
    {
        Utility::ensureNot(false, false, 1);
    }

    /**
     * @test
     * @covers ::ensureNot
     * @expectedException Exception
     */
    public function ensureNotBaseException()
    {
        Utility::ensureNot(false, is_string(1));
    }

    /**
     * @test
     * @covers ::ensureNot
     * @expectedException Exception
     * @expectedExceptionMessage bah
     */
    public function ensureNotUserMessage()
    {
        Utility::ensureNot(false, is_string(1), 'bah');
    }

    /**
     * @test
     * @covers ::ensureNot
     * @expectedException Exception
     * @expectedExceptionMessage bah
     */
    public function ensureNotDynamicException()
    {
        Utility::ensureNot(false, is_string(1), 'Exception', ['bah']);
    }

    /**
     * @test
     * @covers ::ensureNot
     * @expectedException \TraderInteractive\HttpException
     * @expectedExceptionMessage bah
     * @expectedExceptionCode    404
     */
    public function ensureNotDynamicExceptionWithAlias()
    {
        Utility::ensureNot(false, is_string(1), 'http', ['bah', 404, 404]);
    }

    /**
     * @test
     * @covers ::ensureNot
     * @expectedException Exception
     * @expectedExceptionMessage foo
     * @expectedExceptionCode    2
     */
    public function ensureNotException()
    {
        Utility::ensureNot(false, false, new Exception('foo', 2));
    }

    /**
     * @test
     * @covers ::ensure
     */
    public function ensureSuccess()
    {
        $this->assertTrue(Utility::ensure(true, is_string('boo')));
    }

    /**
     * @test
     * @covers ::ensure
     */
    public function ensureSuccessWithErrorObject()
    {
        $error = new Error('the error');
        $this->assertTrue(Util::ensure(true, is_string('foo'), $error));
    }

    /**
     * @test
     * @covers ::ensure
     * @expectedException InvalidArgumentException
     */
    public function ensureBadArg()
    {
        Utility::ensure(false, true, 1);
    }

    /**
     * @test
     * @covers ::ensure
     * @expectedException Exception
     */
    public function ensureBaseException()
    {
        Utility::ensure(true, is_string(1));
    }

    /**
     * @test
     * @covers ::ensure
     * @expectedException Exception
     * @expectedExceptionMessage bah
     */
    public function ensureUserMessage()
    {
        Utility::ensure(true, is_string(1), 'bah');
    }

    /**
     * @test
     * @covers ::ensure
     * @expectedException Exception
     * @expectedExceptionMessage bah
     */
    public function ensureDynamicException()
    {
        Utility::ensure(true, is_string(1), 'Exception', ['bah']);
    }

    /**
     * @test
     * @covers ::ensure
     * @expectedException \TraderInteractive\HttpException
     * @expectedExceptionMessage bah
     * @expectedExceptionCode    404
     */
    public function ensureDynamicExceptionWithAlias()
    {
        Utility::ensure(true, is_string(1), 'http', ['bah', 404, 404]);
    }

    /**
     * @test
     * @covers ::ensure
     * @expectedException Exception
     * @expectedExceptionMessage foo
     * @expectedExceptionCode    2
     */
    public function ensureException()
    {
        Utility::ensure(true, false, new Exception('foo', 2));
    }

    /**
     * @test
     * @covers ::ensure
     */
    public function ensureThrowsErrorObject()
    {
        $error = new TypeError('the error');
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage($error->getMessage());
        Utility::ensure(true, false, $error);
    }

    /**
     * @test
     * @covers ::setExceptionAliases
     * @covers ::getExceptionAliases
     */
    public function setExceptionAliasesGetSet()
    {
        $exceptionAliases = ['shortNameOne' => 'fullNameOne', 'shortNameTwo' => 'fullNameTwo'];
        Utility::setExceptionAliases($exceptionAliases);
        $this->assertSame($exceptionAliases, Utility::getExceptionAliases());
    }
}
