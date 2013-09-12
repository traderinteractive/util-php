<?php
/**
 * Defines the TimeUtilTest class
 */

namespace DominionEnterprises;
use DominionEnterprises\TimeUtil as T;

/**
 * Test class for \DominionEnterprises\TimeUtil.
 */
final class TimeUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getAnsiSqlTimestamp_basic()
    {
        date_default_timezone_set('America/New_York');
        $this->assertSame("(TIMESTAMP'2013-05-02 10:57:08')", T::getAnsiSqlTimestamp(1367506628));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $unixTimestamp was not an int
     */
    public function getAnsiSqlTimestamp_nonInt()
    {
        T::getAnsiSqlTimestamp('1367506628');
    }

    /**
     * @test
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
