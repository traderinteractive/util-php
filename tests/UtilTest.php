<?php

namespace TraderInteractive;

use Throwable;
use TraderInteractive\Util as Utility;
use ErrorException;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

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
     */
    public function raiseExceptionThrowsErrorException()
    {
        $this->expectException(ErrorException::class);
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
     */
    public function throwIfNotTypeStringFailure()
    {
        $this->expectException(InvalidArgumentException::class);
        Utility::throwIfNotType(['string' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     */
    public function throwIfNotTypeBoolFailure()
    {
        $this->expectException(InvalidArgumentException::class);
        Utility::throwIfNotType(['bool' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     */
    public function throwIfNotTypeNullFailure()
    {
        $this->expectException(InvalidArgumentException::class);
        Utility::throwIfNotType(['null' => 2]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     */
    public function throwIfNotTypeIntFailure()
    {
        $this->expectException(InvalidArgumentException::class);
        Utility::throwIfNotType(['int' => [1, 'not an int']]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     */
    public function throwIfNotTypeNotStringTypeArg()
    {
        $this->expectException(InvalidArgumentException::class);
        Utility::throwIfNotType([1]);
    }

    /**
     * @test
     * @covers ::throwIfNotType
     */
    public function throwIfNotTypeBadFunctionName()
    {
        $this->expectException(InvalidArgumentException::class);
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
     */
    public function throwIfNotTypeWhitespaceFailure()
    {
        $this->expectException(InvalidArgumentException::class);
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
     */
    public function ensureNotBadArg()
    {
        $this->expectException(InvalidArgumentException::class);
        Utility::ensureNot(false, false, 1);
    }

    /**
     * @test
     * @covers ::ensureNot
     */
    public function ensureNotBaseException()
    {
        $this->expectException(Exception::class);
        Utility::ensureNot(false, is_string(1));
    }

    /**
     * @test
     * @covers ::ensureNot
     */
    public function ensureNotUserMessage()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('bah');
        Utility::ensureNot(false, is_string(1), 'bah');
    }

    /**
     * @test
     * @covers ::ensureNot
     */
    public function ensureNotDynamicException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('bah');
        Utility::ensureNot(false, is_string(1), 'Exception', ['bah']);
    }

    /**
     * @test
     * @covers ::ensureNot
     */
    public function ensureNotDynamicExceptionWithAlias()
    {
        $this->expectException(\TraderInteractive\HttpException::class);
        $this->expectExceptionMessage('bah');
        $this->expectExceptionCode('404');
        Utility::ensureNot(false, is_string(1), 'http', ['bah', 404, 404]);
    }

    /**
     * @test
     * @covers ::ensureNot
     */
    public function ensureNotException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('foo');
        $this->expectExceptionCode('2');
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
    public function ensureBadArg()
    {
        $this->expectException(InvalidArgumentException::class);
        Utility::ensure(false, true, 1);
    }

    /**
     * @test
     * @covers ::ensure
     */
    public function ensureBaseException()
    {
        $this->expectException(Exception::class);
        Utility::ensure(true, is_string(1));
    }

    /**
     * @test
     * @covers ::ensure
     */
    public function ensureUserMessage()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('bah');
        Utility::ensure(true, is_string(1), 'bah');
    }

    /**
     * @test
     * @covers ::ensure
     */
    public function ensureDynamicException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('bah');
        Utility::ensure(true, is_string(1), 'Exception', ['bah']);
    }

    /**
     * @test
     * @covers ::ensure
     */
    public function ensureDynamicExceptionWithAlias()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('bah');
        $this->expectExceptionCode('404');
        Utility::ensure(true, is_string(1), 'http', ['bah', 404, 404]);
    }

    /**
     * @test
     * @covers ::ensure
     */
    public function ensureException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('foo');
        $this->expectExceptionCode('2');
        Utility::ensure(true, false, new Exception('foo', 2));
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
