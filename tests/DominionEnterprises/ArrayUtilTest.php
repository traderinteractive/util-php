<?php
/**
 * Defines the \DominionEnterprises\ArrayUtilTest class
 */

namespace DominionEnterprises;
use DominionEnterprises\ArrayUtil as A;

/**
 * Test class for \DominionEnterprises\ArrayUtil.
 */
final class ArrayUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \DominionEnterprises\ArrayUtil::get
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
     * @covers \DominionEnterprises\ArrayUtil::copyIfKeysExist
     */
    public function copyIfKeysExist()
    {
        $source = array('a' => 'foo', 'b' => 'bar');

        $result = array();
        A::copyIfKeysExist($source, $result, array('foo' => 'a', 'bar' => 'b'));

        $this->assertSame(array('foo' => 'foo', 'bar' => 'bar'), $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\ArrayUtil::merge
     */
    public function merge()
    {
        $arrayOne = array(
            'a' => 'foo',
            'b' => 'bar',
            'c' => array(
                'A' => 1,
                'B' => 2,
            ),
            'd' => null,
            'e' => 123,
        );

        $arrayTwo = array(
            'b' => 'baz',
            'c' => array(
                'A' => 1,
                'C' => 3,
            ),
            'd' => 'foo',
            'e' => null,
        );

        $expected = array(
            'a' => 'foo',
            'b' => 'baz',
            'c' => array(
                'A' => 1,
                'B' => 2,
                'C' => 3,
            ),
            'd' => 'foo',
            'e' => null,
        );

        $this->assertSame($expected, A::merge($arrayOne, $arrayTwo));
    }

    /**
     * @test
     * @covers \DominionEnterprises\ArrayUtil::tryGet
     */
    public function tryGet_nullKey()
    {
        $value = 'filler';
        $this->assertFalse(A::tryGet(array(), null, $value));
        $this->assertSame(null, $value);
    }

    /**
     * @test
     * @covers \DominionEnterprises\ArrayUtil::tryGet
     */
    public function tryGet_classForKey()
    {
        $value = 'filler';
        $this->assertFalse(A::tryGet(array(), new \stdClass(), $value));
        $this->assertSame(null, $value);
    }

    /**
     * @test
     * @covers \DominionEnterprises\ArrayUtil::tryGet
     */
    public function tryGet_valueStringKey()
    {
        $value = 'filler';
        $this->assertTrue(A::tryGet(array('a' => 1), 'a', $value));
        $this->assertSame(1, $value);
    }

    /**
     * @test
     * @covers \DominionEnterprises\ArrayUtil::tryGet
     */
    public function tryGet_valueIntegerKey()
    {
        $value = 'filler';
        $this->assertTrue(A::tryGet(array(1.1, 2.2), 0, $value));
        $this->assertSame(1.1, $value);
    }

    /**
     * @test
     * @covers \DominionEnterprises\ArrayUtil::project
     */
    public function project_basicUse()
    {
        $expected = array(2, 'boo' => 4);
        $result = A::project(array(array('key1' => 1, 'key2' => 2), 'boo' => array('key1' => 3, 'key2' => 4)), 'key2');

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\ArrayUtil::project
     * @expectedException \InvalidArgumentException
     */
    public function project_strictKeyFail()
    {
        A::project(array(array('key1' => 1, 'key2' => 2), array('key1' => 3)), 'key2');
    }

    /**
     * @test
     * @covers \DominionEnterprises\ArrayUtil::project
     */
    public function project_strictKeyFalse()
    {
        $expected = array(1 => 4);
        $result = A::project(array(array('key1' => 1), array('key1' => 3, 'key2' => 4)), 'key2', false);

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     * @covers \DominionEnterprises\ArrayUtil::project
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $strictKeyCheck was not a bool
     */
    public function project_strictKeyNotBool()
    {
        A::project(array(), 'not under test', 1);
    }

    /**
     * @test
     * @covers \DominionEnterprises\ArrayUtil::project
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
     * @covers \DominionEnterprises\ArrayUtil::where
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
     * @covers \DominionEnterprises\ArrayUtil::where
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
     * @covers \DominionEnterprises\ArrayUtil::where
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
     * @covers \DominionEnterprises\ArrayUtil::where
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
     * @covers \DominionEnterprises\ArrayUtil::where
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
     * @covers \DominionEnterprises\ArrayUtil::embedInto
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
     * @covers \DominionEnterprises\ArrayUtil::embedInto
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
     * @covers \DominionEnterprises\ArrayUtil::embedInto
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
     * @covers \DominionEnterprises\ArrayUtil::embedInto
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
     * @covers \DominionEnterprises\ArrayUtil::embedInto
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
     * @covers \DominionEnterprises\ArrayUtil::embedInto
     */
    public function embedInto_noItems()
    {
        $this->assertSame(array(array('result' => 'foo')), A::embedInto(array(), 'result', array(array('result' => 'foo'))));
    }

    /**
     * Basic usage of fillIfKeysExist()
     *
     * @test
     * @covers \DominionEnterprises\ArrayUtil::fillIfKeysExist
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
