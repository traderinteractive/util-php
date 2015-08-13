<?php
/**
 * Defines the \DominionEnterprises\Util\TimeTest class
 */

namespace DominionEnterprises\Util;

use DominionEnterprises\Util\Time as T;

/**
 * @coversDefaultClass \DominionEnterprises\Util\Time
 */
final class TimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers ::getAnsiSqlTimestamp
     */
    public function getAnsiSqlTimestampBasic()
    {
        date_default_timezone_set('America/New_York');
        $this->assertSame("(TIMESTAMP'2013-05-02 10:57:08')", T::getAnsiSqlTimestamp(1367506628));
    }

    /**
     * @test
     * @covers ::getAnsiSqlTimestamp
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $unixTimestamp was not an int
     */
    public function getAnsiSqlTimestampNonInt()
    {
        T::getAnsiSqlTimestamp('1367506628');
    }

    /**
     * @test
     * @covers ::inMillis
     */
    public function inMillis()
    {
        $beforeSeconds = time();
        $milliseconds = T::inMillis();
        $afterSecondsPlus = time() + 1;

        $this->assertGreaterThanOrEqual($beforeSeconds * 1000, $milliseconds);
        $this->assertLessThanOrEqual($afterSecondsPlus * 1000, $milliseconds);
    }
}
