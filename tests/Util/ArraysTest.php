<?php
/**
 * Defines the \DominionEnterprises\Util\ArraysTest class
 */

namespace DominionEnterprises\Util;
use DominionEnterprises\Util\Arrays as A;

/**
 * @coversDefaultClass \DominionEnterprises\Util\Arrays
 */
final class ArraysTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers ::get
     */
    public function get()
    {
        $array = ['a' => 'foo', 'b' => 'bar'];
        $this->assertSame('foo', A::get($array, 'a'));
        $this->assertSame(null, A::get($array, 'c'));
        $this->assertSame('baz', A::get($array, 'c', 'baz'));
    }

    /**
     * @test
     * @covers ::getIfSet
     */
    public function getIfSet()
    {
        $array = ['a' => 'foo', 'b' => null];
        $this->assertSame('foo', A::getIfSet($array, 'a'));
        $this->assertSame('bar', A::getIfSet($array, 'b', 'bar'));
        $this->assertSame(null, A::getIfSet($array, 'c'));
        $this->assertSame('bar', A::getIfSet($array, 'c', 'bar'));
    }

    /**
     * @test
     * @covers ::copyIfKeysExist
     */
    public function copyIfKeysExist()
    {
        $source = ['a' => 'foo', 'b' => 'bar'];

        $result = [];
        A::copyIfKeysExist($source, $result, ['foo' => 'a', 'bar' => 'b']);

        $this->assertSame(['foo' => 'foo', 'bar' => 'bar'], $result);
    }

    /**
     * Verify behavior with numeric array $keyMap
     *
     * @test
     * @covers ::copyIfKeysExist
     */
    public function copyIfKeysExist_numericKeyMap()
    {
        $source = ['a' => 'foo', 'b' => 'bar', 'd' => 'baz'];
        $result = [];
        A::copyIfKeysExist($source, $result, ['a', 'b', 'c']);
        $this->assertSame(['a' => 'foo', 'b' => 'bar'], $result);
    }

    /**
     * Verify basic behavior of copyIfSet()
     *
     * @test
     * @covers ::copyIfSet
     */
    public function copyIfSet()
    {
        $source = ['a' => 'foo', 'b' => null, 'd' => 'baz'];
        $result = [];
        A::copyIfSet($source, $result, ['alpha' => 'a', 'beta' => 'b', 'charlie' => 'c', 'delta' => 'd']);
        $this->assertSame(['alpha' => 'foo', 'delta' => 'baz'], $result);
    }

    /**
     * Verify behavior of copyIfSet() with numeric array $keyMap
     *
     * @test
     * @covers ::copyIfSet
     */
    public function copyIfSet_numericKeyMap()
    {
        $source = ['a' => 'foo', 'b' => null, 'd' => 'baz'];
        $result = [];
        A::copyIfSet($source, $result, ['a', 'b', 'c', 'd']);
        $this->assertSame(['a' => 'foo', 'd' => 'baz'], $result);
    }

    /**
     * @test
     * @covers ::tryGet
     */
    public function tryGet_nullKey()
    {
        $value = 'filler';
        $this->assertFalse(A::tryGet([], null, $value));
        $this->assertSame(null, $value);
    }

    /**
     * @test
     * @covers ::tryGet
     */
    public function tryGet_classForKey()
    {
        $value = 'filler';
        $this->assertFalse(A::tryGet([], new \stdClass(), $value));
        $this->assertSame(null, $value);
    }

    /**
     * @test
     * @covers ::tryGet
     */
    public function tryGet_valueStringKey()
    {
        $value = 'filler';
        $this->assertTrue(A::tryGet(['a' => 1], 'a', $value));
        $this->assertSame(1, $value);
    }

    /**
     * @test
     * @covers ::tryGet
     */
    public function tryGet_valueIntegerKey()
    {
        $value = 'filler';
        $this->assertTrue(A::tryGet([1.1, 2.2], 0, $value));
        $this->assertSame(1.1, $value);
    }

    /**
     * @test
     * @covers ::project
     */
    public function project_basicUse()
    {
        $expected = [2, 'boo' => 4];
        $result = A::project([['key1' => 1, 'key2' => 2], 'boo' => ['key1' => 3, 'key2' => 4]], 'key2');

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     * @covers ::project
     * @expectedException \InvalidArgumentException
     */
    public function project_strictKeyFail()
    {
        A::project([['key1' => 1, 'key2' => 2], ['key1' => 3]], 'key2');
    }

    /**
     * @test
     * @covers ::project
     */
    public function project_strictKeyFalse()
    {
        $expected = [1 => 4];
        $result = A::project([['key1' => 1], ['key1' => 3, 'key2' => 4]], 'key2', false);

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     * @covers ::project
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $strictKeyCheck was not a bool
     */
    public function project_strictKeyNotBool()
    {
        A::project([], 'not under test', 1);
    }

    /**
     * @test
     * @covers ::project
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage a value in $input was not an array
     */
    public function project_inputValueNotArray()
    {
        A::project([1], 'not under test');
    }

    /**
     * Verifies basic usage for where() with exact matching
     *
     * @test
     * @covers ::where
     */
    public function where_basicUsage()
    {
        $people = [
            ['name' => 'Tom', 'score' => '0'],
            ['name' => 'Dick', 'score' => 0],
            ['name' => 'Jane'],
        ];

        $expected = [['name' => 'Dick', 'score' => 0]];
        $result = A::where($people, ['score' => 0]);
        $this->assertSame($expected, $result);
    }

    /**
     * Verifies that where() returns empty array when nothing matches
     *
     * @test
     * @covers ::where
     */
    public function where_returnsEmptyArray()
    {
        $people = [
            ['name' => 'Tom', 'score' => '0'],
            ['name' => 'Dick', 'score' => 0],
            ['name' => 'Harry', 'score' => 0.0],
        ];

        $result = A::where($people, ['score' => false]);
        $this->assertSame([], $result);
    }

    /**
     * Verifies use of multiple conditions
     *
     * @test
     * @covers ::where
     */
    public function where_withMultipleConditions()
    {
        $people = [
            ['name' => 'Tom', 'score' => 1, 'extra' => 'abc'],
            ['name' => 'Dick', 'score' => 1, 'extra' => false],
            ['name' => 'Dick', 'score' => 0, 'extra' => 123],
        ];

        $expected = [['name' => 'Dick', 'score' => 1, 'extra' => false]];
        $result = A::where($people, ['name' => 'Dick', 'score' => 1]);
        $this->assertSame($expected, $result);
    }

    /**
     * Verifies use of multiple conditions
     *
     * @test
     * @covers ::where
     */
    public function where_returnsMultipleResults()
    {
        $array = [
            ['key 1' => 'a', 'key 2' => 'b'],
            ['key 1' => 'c', 'key 2' => 'd'],
            ['key 1' => 'a', 'key 2' => 'c'],
        ];

        $expected = [
            ['key 1' => 'a', 'key 2' => 'b'],
            ['key 1' => 'a', 'key 2' => 'c'],
        ];

        $result = A::where($array, ['key 1' => 'a']);
        $this->assertSame($expected, $result);
    }

    /**
     * @test
     * @covers ::where
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage a value in $array was not an array
     */
    public function where_inputValueNotArray()
    {
        A::where([1], []);
    }

    /**
     * Verifies that embedInto works well with adding new items into an existing array.
     *
     * @test
     * @covers ::embedInto
     */
    public function embedInto_basicUse()
    {
        $this->assertSame(
            [
                ['request' => ['image' => 'foo'], 'result' => ['exception' => 'exception 1']],
                ['request' => ['image' => 'bar'], 'result' => ['exception' => 'exception 2']],
            ],
            A::embedInto(
                [['exception' => 'exception 1'], ['exception' => 'exception 2']],
                'result',
                [['request' => ['image' => 'foo']], ['request' => ['image' => 'bar']]]
            )
        );
    }

    /**
     * Verifies that embedInto works well with creating new records.
     *
     * @test
     * @covers ::embedInto
     */
    public function embedInto_emptyDestination()
    {
        $this->assertSame(
            [['request' => ['image' => 'foo']], ['request' => ['image' => 'bar']]],
            A::embedInto([['image' => 'foo'], ['image' => 'bar']], 'request')
        );
    }

    /**
     * Verifies that embedInto requires string for fieldname
     *
     * @test
     * @covers ::embedInto
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $fieldName was not a string
     */
    public function embedInto_numericFieldName()
    {
        A::embedInto([], 5);
    }

    /**
     * Verifies that embedInto requires destination entries to be arrays.
     *
     * @test
     * @covers ::embedInto
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage a value in $destination was not an array
     */
    public function embedInto_nonArrayDestinationItems()
    {
        A::embedInto(['one' => 0], 'result', ['one' => 0]);
    }

    /**
     * Verifies that embedInto refuses to overwrite field names.
     *
     * @test
     * @covers ::embedInto
     * @expectedException Exception
     */
    public function embedInto_existingFieldName()
    {
        A::embedInto(['new'], 'result', [['result' => 'old']]);
    }

    /**
     * Verifies that embedInto does nothing with 0 items to embed.
     *
     * @test
     * @covers ::embedInto
     */
    public function embedInto_noItems()
    {
        $this->assertSame([['result' => 'foo']], A::embedInto([], 'result', [['result' => 'foo']]));
    }

    /**
     * @test
     * @covers ::embedInto
     */
    public function embedInto_overwrite()
    {
        $this->assertSame([['key' => true]], A::embedInto([true], 'key', [['key' => false]], true));
    }

    /**
     * @test
     * @covers ::embedInto
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $overwrite was not a bool
     */
    public function embedInto_overwriteNotBool()
    {
        A::embedInto([], 'key', [], 1);
    }

    /**
     * Basic usage of fillIfKeysExist()
     *
     * @test
     * @covers ::fillIfKeysExist
     */
    public function fillIfKeysExist()
    {
        $template = ['a' => null, 'b' => null, 'c' => null, 'd' => null, 'e' => null];

        $actual = A::fillIfKeysExist($template, ['a' => 1, 'c' => 1, 'e' => 1]);

        $expected = ['a' => 1, 'b' => null, 'c' => 1, 'd' => null, 'e' => 1];

        $this->assertSame($expected, $actual);
    }

    /**
     * Basic usage of extract()
     *
     * @test
     * @covers ::extract
     * @uses \DominionEnterprises\Util\Arrays::get
     */
    public function extract()
    {
        $input = [
            ['key' => 'foo', 'value' => 'bar', 'extra' => 'abc'],
            ['extra' => 123, 'key' => 'baz', 'value' => 'fez'],
            ['value' => 'duplicate1', 'extra' => true, 'key' => 'boo'],
            ['extra' => true, 'key' => 'noValue'],
            ['value' => 'duplicate2', 'extra' => true, 'key' => 'boo'],
        ];

        $expected = ['foo' => 'bar', 'baz' => 'fez', 'boo' => 'duplicate2', 'noValue' => null];

        $this->assertSame($expected, A::extract($input, 'key', 'value'));
    }

    /**
     * Basic usage of extract() with 'takeFirst' option
     *
     * @test
     * @covers ::extract
     * @uses \DominionEnterprises\Util\Arrays::get
     */
    public function extract_takeFirst()
    {
        $input = [
            ['key' => 'foo', 'value' => 'bar', 'extra' => 'abc'],
            ['extra' => 123, 'key' => 'baz', 'value' => 'fez'],
            ['value' => 'duplicate1', 'extra' => true, 'key' => 'boo'],
            ['extra' => true, 'key' => 'noValue'],
            ['value' => 'duplicate2', 'extra' => true, 'key' => 'boo'],
        ];

        $expected = ['foo' => 'bar', 'baz' => 'fez', 'boo' => 'duplicate1', 'noValue' => null];

        $this->assertSame($expected, A::extract($input, 'key', 'value', 'takeFirst'));
    }

    /**
     * Basic usage of extract() with 'throw' option
     *
     * @test
     * @covers ::extract
     * @uses \DominionEnterprises\Util\Arrays::get
     * @expectedException \Exception
     * @expectedExceptionMessage Duplicate entry for 'boo' found.
     */
    public function extract_throwOnDuplicate()
    {
        $input = [
            ['key' => 'foo', 'value' => 'bar', 'extra' => 'abc'],
            ['extra' => 123, 'key' => 'baz', 'value' => 'fez'],
            ['value' => 'duplicate1', 'extra' => true, 'key' => 'boo'],
            ['extra' => true, 'key' => 'noValue'],
            ['value' => 'duplicate2', 'extra' => true, 'key' => 'boo'],
        ];

        A::extract($input, 'key', 'value', 'throw');
    }

    /**
     * Verify behavior when a single dimensional array is given to extract().
     *
     * @test
     * @covers ::extract
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $arrays was not a multi-dimensional array
     */
    public function extract_withSingleDimensionalArray()
    {
        A::extract(['key' => 'foo', 'value' => 'bar', 'extra' => 'abc'], 'key', 'value');
    }

    /**
     * Verify behavior when $arrays contain a invalid key value in the supplied $keyIndex field.
     *
     * @test
     * @covers ::extract
     * @uses \DominionEnterprises\Util\Arrays::get
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Value for $arrays[1][key] was not a string or integer
     */
    public function extract_withInvalidKeyValue()
    {
        $input = [
            ['key' => 'foo', 'value' => 'bar', 'extra' => 'abc'],
            ['extra' => 123, 'key' => [], 'value' => 'fez'],
        ];

        A::extract($input, 'key', 'value', 'throw');
    }

    /**
     * Verify behavior when $keyIndex is not a string or integer
     *
     * @test
     * @covers ::extract
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $keyIndex was not a string or integer
     */
    public function extract_withInvalidKeyIndex()
    {
        A::extract([], true, 'value');
    }

    /**
     * Verify behavior when $valueIndex is not a string or integer
     *
     * @test
     * @covers ::extract
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $valueIndex was not a string or integer
     */
    public function extract_withInvalidValueIndex()
    {
        A::extract([], 'key', []);
    }

    /**
     * Verify behavior when $duplicateBehavior is not valid
     *
     * @test
     * @covers ::extract
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $duplicateBehavior was not 'takeFirst', 'takeLast', or 'throw'
     */
    public function extract_withInvalidDuplicateBehavior()
    {
        A::extract([], 'key', 'value', 'invalid');
    }

    /**
     * Verify basic behavior of getFirstSet()
     *
     * @test
     * @covers ::getFirstSet
     */
    public function getFirstSet()
    {
        $this->assertSame('bar', A::getFirstSet(['foo', null, 'bar'], [1, 2]));
    }

    /**
     * Verify getFirstSet() returns default value
     *
     * @test
     * @covers ::getFirstSet
     */
    public function getFirstSet_withDefault()
    {
        $this->assertSame('baz', A::getFirstSet(['foo', null, 'bar'], [1, 4], 'baz'));
    }

    /**
     * Verifiy basic behavior of partition()
     *
     * @test
     * @covers ::partition
     */
    public function partition()
    {
        $this->assertSame([['a'], ['b'], ['c']], A::partition(['a', 'b', 'c'], 3));
    }

    /**
     * Verify partition() behavior when $input array contains less items than than $partitionCount.
     *
     * @test
     * @covers ::partition
     */
    public function partition_inputLessThanPartitionCount()
    {
        $this->assertSame([['a'], ['b'], ['c']], A::partition(['a', 'b', 'c'], 4));
    }

    /**
     * Verify remainder of $input array is front-loaded in partition().
     *
     * @test
     * @covers ::partition
     */
    public function partition_withRemainder()
    {
        $this->assertSame([['a', 'b'], ['c'], ['d']], A::partition(['a', 'b', 'c', 'd'], 3));
    }

    /**
     * Verify remainder of $input array is front-loaded in partition().
     *
     * @test
     * @covers ::partition
     */
    public function partition_withMultipleRemainder()
    {
        $this->assertSame([['a', 'b'], ['c', 'd'], ['e']], A::partition(['a', 'b', 'c', 'd', 'e'], 3));
    }

    /**
     * Verify partition() handles empty $input array.
     *
     * @test
     * @covers ::partition
     */
    public function partition_emptyInput()
    {
        $this->assertSame([], A::partition([], 2));
    }

    /**
     * Verifiy behavior of partition() with $partitionCount of 1.
     *
     * @test
     * @covers ::partition
     */
    public function partition_onePartition()
    {
        $this->assertSame([['a', 'b', 'c']], A::partition(['a', 'b', 'c'], 1));
    }

    /**
     * Verifiy partition() throws with negative $partitionCount.
     *
     * @test
     * @covers ::partition
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $partitionCount must be a positive integer
     */
    public function partition_negativePartitionCount()
    {
        A::partition(['a', 'b', 'c'], -1);
    }

    /**
     * Verifiy partition() throws with 0 $partitionCount.
     *
     * @test
     * @covers ::partition
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $partitionCount must be a positive integer
     */
    public function partition_zeroPartitionCount()
    {
        A::partition(['a', 'b', 'c'], 0);
    }

    /**
     * Verifiy partition() throws with non-integer $partitionCount.
     *
     * @test
     * @covers ::partition
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $partitionCount must be a positive integer
     */
    public function partition_nonIntegerPartitionCount()
    {
        A::partition(['a', 'b', 'c'], 'not an int');
    }

    /**
     * Verifiy partition() preserves numeric keys.
     *
     * @test
     * @covers ::partition
     */
    public function partition_preserveNumericKeys()
    {
        $this->assertSame(
            [[0 => 'a', 1 => 'b'], [2 => 'c', 3 => 'd'], [4 => 'e']],
            A::partition(['a', 'b', 'c', 'd', 'e'], 3, true)
        );
    }

    /**
     * Verifiy partition() preserves associative keys.
     *
     * @test
     * @covers ::partition
     */
    public function partition_preserveAssociativeKeys()
    {
        $this->assertSame(
            [['a' => 0, 'b' => 1], ['c' => 2, 'd' => 3], ['e' => 4]],
            A::partition(['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3, 'e' => 4], 3)
        );
    }

    /**
     * Verifiy partition() throws with non-boolean $preserveKeys.
     *
     * @test
     * @covers ::partition
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $preserveKeys must be a boolean value
     */
    public function partition_nonBoolPreserveKeys()
    {
        A::partition(['a', 'b', 'c'], 3, 'not a bool');
    }
}
