<?php
/**
 * Defines the \DominionEnterprises\Util\ArraysTest class
 */

namespace DominionEnterprises\Util;
use DominionEnterprises\Util\Arrays as A;

/**
 * Test class for \DominionEnterprises\ArrayUtil.
 */
final class ArraysTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \DominionEnterprises\Util\Arrays::get
     */
    public function get()
    {
        $array = array('a' => 'foo', 'b' => 'bar');
        $this->assertSame('foo', A::get($array, 'a'));
        $this->assertSame(null, A::get($array, 'c'));
        $this->assertSame('baz', A::get($array, 'c', 'baz'));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Util\Arrays::copyIfKeysExist
     */
    public function copyIfKeysExist()
    {
        $source = array('a' => 'foo', 'b' => 'bar');

        $result = array();
        A::copyIfKeysExist($source, $result, array('foo' => 'a', 'bar' => 'b'));

        $this->assertSame(array('foo' => 'foo', 'bar' => 'bar'), $result);
    }

    /**
     * Verify behavior with numeric array $keyMap
     *
     * @test
     * @covers \DominionEnterprises\Util\Arrays::copyIfKeysExist
     */
    public function copyIfKeysExist_numericKeyMap()
    {
        $source = array('a' => 'foo', 'b' => 'bar', 'd' => 'baz');
        $result = array();
        A::copyIfKeysExist($source, $result, array('a', 'b', 'c'));
        $this->assertSame(array('a' => 'foo', 'b' => 'bar'), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Util\Arrays::tryGet
     */
    public function tryGet_nullKey()
    {
        $value = 'filler';
        $this->assertFalse(A::tryGet(array(), null, $value));
        $this->assertSame(null, $value);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Util\Arrays::tryGet
     */
    public function tryGet_classForKey()
    {
        $value = 'filler';
        $this->assertFalse(A::tryGet(array(), new \stdClass(), $value));
        $this->assertSame(null, $value);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Util\Arrays::tryGet
     */
    public function tryGet_valueStringKey()
    {
        $value = 'filler';
        $this->assertTrue(A::tryGet(array('a' => 1), 'a', $value));
        $this->assertSame(1, $value);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Util\Arrays::tryGet
     */
    public function tryGet_valueIntegerKey()
    {
        $value = 'filler';
        $this->assertTrue(A::tryGet(array(1.1, 2.2), 0, $value));
        $this->assertSame(1.1, $value);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Util\Arrays::project
     */
    public function project_basicUse()
    {
        $expected = array(2, 'boo' => 4);
        $result = A::project(array(array('key1' => 1, 'key2' => 2), 'boo' => array('key1' => 3, 'key2' => 4)), 'key2');

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Util\Arrays::project
     * @expectedException \InvalidArgumentException
     */
    public function project_strictKeyFail()
    {
        A::project(array(array('key1' => 1, 'key2' => 2), array('key1' => 3)), 'key2');
    }

    /**
     * @test
     * @covers \DominionEnterprises\Util\Arrays::project
     */
    public function project_strictKeyFalse()
    {
        $expected = array(1 => 4);
        $result = A::project(array(array('key1' => 1), array('key1' => 3, 'key2' => 4)), 'key2', false);

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Util\Arrays::project
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $strictKeyCheck was not a bool
     */
    public function project_strictKeyNotBool()
    {
        A::project(array(), 'not under test', 1);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Util\Arrays::project
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage a value in $input was not an array
     */
    public function project_inputValueNotArray()
    {
        A::project(array(1), 'not under test');
    }

    /**
     * Verifies basic usage for where() with exact matching
     *
     * @test
     * @covers \DominionEnterprises\Util\Arrays::where
     */
    public function where_basicUsage()
    {
        $people = array(
            array('name' => 'Tom', 'score' => '0'),
            array('name' => 'Dick', 'score' => 0),
            array('name' => 'Jane'),
        );

        $expected = array(array('name' => 'Dick', 'score' => 0));
        $result = A::where($people, array('score' => 0));
        $this->assertSame($expected, $result);
    }

    /**
     * Verifies that where() returns empty array when nothing matches
     *
     * @test
     * @covers \DominionEnterprises\Util\Arrays::where
     */
    public function where_returnsEmptyArray()
    {
        $people = array(
            array('name' => 'Tom', 'score' => '0'),
            array('name' => 'Dick', 'score' => 0),
            array('name' => 'Harry', 'score' => 0.0),
        );

        $result = A::where($people, array('score' => false));
        $this->assertSame(array(), $result);
    }

    /**
     * Verifies use of multiple conditions
     *
     * @test
     * @covers \DominionEnterprises\Util\Arrays::where
     */
    public function where_withMultipleConditions()
    {
        $people = array(
            array('name' => 'Tom', 'score' => 1, 'extra' => 'abc'),
            array('name' => 'Dick', 'score' => 1, 'extra' => false),
            array('name' => 'Dick', 'score' => 0, 'extra' => 123),
        );

        $expected = array(array('name' => 'Dick', 'score' => 1, 'extra' => false));
        $result = A::where($people, array('name' => 'Dick', 'score' => 1));
        $this->assertSame($expected, $result);
    }

    /**
     * Verifies use of multiple conditions
     *
     * @test
     * @covers \DominionEnterprises\Util\Arrays::where
     */
    public function where_returnsMultipleResults()
    {
        $array = array(
            array('key 1' => 'a', 'key 2' => 'b'),
            array('key 1' => 'c', 'key 2' => 'd'),
            array('key 1' => 'a', 'key 2' => 'c'),
        );

        $expected = array(
            array('key 1' => 'a', 'key 2' => 'b'),
            array('key 1' => 'a', 'key 2' => 'c'),
        );

        $result = A::where($array, array('key 1' => 'a'));
        $this->assertSame($expected, $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\Util\Arrays::where
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage a value in $array was not an array
     */
    public function where_inputValueNotArray()
    {
        A::where(array(1), array());
    }

    /**
     * Verifies that embedInto works well with adding new items into an existing array.
     *
     * @test
     * @covers \DominionEnterprises\Util\Arrays::embedInto
     */
    public function embedInto_basicUse()
    {
        $this->assertSame(
            array(
                array('request' => array('image' => 'foo'), 'result' => array('exception' => 'exception 1')),
                array('request' => array('image' => 'bar'), 'result' => array('exception' => 'exception 2')),
            ),
            A::embedInto(
                array(array('exception' => 'exception 1'), array('exception' => 'exception 2')),
                'result',
                array(array('request' => array('image' => 'foo')), array('request' => array('image' => 'bar')))
            )
        );
    }

    /**
     * Verifies that embedInto works well with creating new records.
     *
     * @test
     * @covers \DominionEnterprises\Util\Arrays::embedInto
     */
    public function embedInto_emptyDestination()
    {
        $this->assertSame(
            array(array('request' => array('image' => 'foo')), array('request' => array('image' => 'bar'))),
            A::embedInto(array(array('image' => 'foo'), array('image' => 'bar')), 'request')
        );
    }

    /**
     * Verifies that embedInto requires string for fieldname
     *
     * @test
     * @covers \DominionEnterprises\Util\Arrays::embedInto
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $fieldName was not a string
     */
    public function embedInto_numericFieldName()
    {
        A::embedInto(array(), 5);
    }

    /**
     * Verifies that embedInto requires destination entries to be arrays.
     *
     * @test
     * @covers \DominionEnterprises\Util\Arrays::embedInto
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage a value in $destination was not an array
     */
    public function embedInto_nonArrayDestinationItems()
    {
        A::embedInto(array('one' => 0), 'result', array('one' => 0));
    }

    /**
     * Verifies that embedInto refuses to overwrite field names.
     *
     * @test
     * @covers \DominionEnterprises\Util\Arrays::embedInto
     * @expectedException Exception
     */
    public function embedInto_existingFieldName()
    {
        A::embedInto(array('new'), 'result', array(array('result' => 'old')));
    }

    /**
     * Verifies that embedInto does nothing with 0 items to embed.
     *
     * @test
     * @covers \DominionEnterprises\Util\Arrays::embedInto
     */
    public function embedInto_noItems()
    {
        $this->assertSame(array(array('result' => 'foo')), A::embedInto(array(), 'result', array(array('result' => 'foo'))));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Util\Arrays::embedInto
     */
    public function embedInto_overwrite()
    {
        $this->assertSame(array(array('key' => true)), A::embedInto(array(true), 'key', array(array('key' => false)), true));
    }

    /**
     * @test
     * @covers \DominionEnterprises\Util\Arrays::embedInto
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $overwrite was not a bool
     */
    public function embedInto_overwriteNotBool()
    {
        A::embedInto(array(), 'key', array(), 1);
    }

    /**
     * Basic usage of fillIfKeysExist()
     *
     * @test
     * @covers \DominionEnterprises\Util\Arrays::fillIfKeysExist
     */
    public function fillIfKeysExist()
    {
        $template = array(
            'a' => null,
            'b' => null,
            'c' => null,
            'd' => null,
            'e' => null,
        );

        $actual = A::fillIfKeysExist($template, array('a' => 1, 'c' => 1, 'e' => 1));

        $expected = array(
            'a' => 1,
            'b' => null,
            'c' => 1,
            'd' => null,
            'e' => 1,
        );

        $this->assertSame($expected, $actual);
    }
}
