<?php
/**
 * Defines the TimeUtilTest class
 */

namespace DominionEnterprises\Tests;
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
        $this->assertSame("(TIMESTAMP'2013-05-02 10:57:08')", T::getAnsiSqlTimestamp(1367506628));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function getAnsiSqlTimestamp_nonInt()
    {
        T::getAnsiSqlTimestamp('1367506628');
    }
}
