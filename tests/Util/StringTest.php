<?php

namespace DominionEnterprises\Util;
use DominionEnterprises\Util\String as S;

/**
 * @coversDefaultClass \DominionEnterprises\Util\String
 */
final class StringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage StringUtil::format() takes at least 2 arguments
     * @covers ::format
     */
    public function format_onlyOneArgumentn()
    {
        S::format('{0} and {1}');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @covers ::format
     * @uses \DominionEnterprises\Util::raiseException
     */
    public function format_nonStringCastableObject()
    {
        S::format('{0} and {1}', new \StdClass(), 'Jill');
    }

    /**
     * @test
     * @covers ::format
     */
    public function format_stringCastableObject()
    {
        $this->assertSame('Jack and Jill', S::format('{0} and {1}', new TestObjectWithToString(), 'Jill'));
    }

    /**
     * @test
     * @covers ::format
     */
    public function format_keysAreRepeatable()
    {
        $this->assertSame('AAA', S::format('{0}{0}{0}', 'A'));
    }

    /**
     * @test
     * @covers ::format
     */
    public function format_keyOrderDoesNotMatter()
    {
        $this->assertSame('ABC', S::format('{2}{1}{0}', 'C', 'B', 'A'));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $format is not a string
     * @covers ::format
     */
    public function format_nonStringFormat()
    {
        S::format(array(), 'C', 'B', 'A');
    }

    /**
     * @test
     * @covers ::endsWith
     */
    public function endsWith_matches()
    {
        $nonSuffix = null;
        $this->assertTrue(S::endsWith('bah', 'h', $nonSuffix));
        $this->assertSame('ba', $nonSuffix);
    }

    /**
     * @test
     * @covers ::endsWith
     */
    public function endsWith_noMatches()
    {
        $nonSuffix = null;
        $this->assertFalse(S::endsWith('bah', 'z', $nonSuffix));
        $this->assertSame('bah', $nonSuffix);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $string is not a string
     * @covers ::endsWith
     */
    public function endsWith_badTypeForSubject()
    {
        S::endsWith(true, '');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $suffix is not a string
     * @covers ::endsWith
     */
    public function endsWith_badTypeForSuffix()
    {
        S::endsWith('', true);
    }

    /**
     * @test
     * @covers ::endsWith
     */
    public function endsWith_emptyBoth()
    {
        $nonSuffix = null;
        $this->assertTrue(S::endsWith('', '', $nonSuffix));
        $this->assertSame('', $nonSuffix);
    }

    /**
     * @test
     * @covers ::endsWith
     */
    public function endsWith_emptySuffix()
    {
        $nonSuffix = null;
        $this->assertTrue(S::endsWith('a', '', $nonSuffix));
        $this->assertSame('a', $nonSuffix);
    }

    /**
     * @test
     * @covers ::endsWith
     */
    public function endsWith_emptySubject()
    {
        $nonSuffix = null;
        $this->assertFalse(S::endsWith('', 'b', $nonSuffix));
        $this->assertSame('', $nonSuffix);
    }

    /**
     * @test
     * @covers ::ellipsize
     */
    public function ellipsize()
    {
        $input = 'Short text is an arbitrary thing.';
        $this->assertSame('', S::ellipsize($input, 0));
        $this->assertSame('.', S::ellipsize($input, 1));
        $this->assertSame('...', S::ellipsize($input, 3));
        $this->assertSame('S...', S::ellipsize($input, 4));
        $this->assertSame('Short text...', S::ellipsize($input, 13));
        $this->assertSame('Short text is an arbitrary th...', S::ellipsize($input, 32));
        $this->assertSame($input, S::ellipsize($input, 33));
        $this->assertSame($input, S::ellipsize($input, 34));
        $this->assertSame($input, S::ellipsize($input, 35));
        $this->assertSame($input, S::ellipsize($input, 50));
    }

    /**
     * @test
     * @covers ::ellipsize
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $maxLength is negative
     */
    public function ellipsize_negativeMaxLength()
    {
        S::ellipsize('foo', -1);
    }

    /**
     * Tests that ellipsize works with a custom suffix.
     *
     * @test
     * @covers ::ellipsize
     */
    public function ellipsize_customSuffix()
    {
        $this->assertSame('Test!', S::ellipsize('Testing', 5, '!'));
    }

    /**
     * Tests that ellipsize fails with an integer instead of a string.
     *
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $string is not a string
     * @covers ::ellipsize
     */
    public function ellipsize_integerInsteadOfString()
    {
        S::ellipsize(null, 10);
    }

    /**
     * Tests that ellipsize fails with a string for $maxLength.
     *
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $maxLength is not an integer
     * @covers ::ellipsize
     */
    public function ellipsize_stringMaxLength()
    {
        S::ellipsize('test', 'a');
    }

    /**
     * Tests that ellipsize fails with an integer for $suffix.
     *
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $suffix is not a string
     * @covers ::ellipsize
     */
    public function ellipsize_integerSuffix()
    {
        S::ellipsize('test', 10, 0);
    }

    /**
     * @test
     * @covers ::ucwords
     */
    public function ucwords()
    {
        $input = 'break-down o\'boy up_town you+me here now,this:place';
        $this->assertSame('Break-Down O\'Boy Up_Town You+Me Here Now,This:Place', S::ucwords($input));
    }

    /**
     * @test
     * @covers ::ucwords
     */
    public function ucwords_optionalDelimiters()
    {
        $input = 'break-down o\'boy up_town you+me here now,this:place';
        $this->assertSame('Break-Down O\'boy Up_town You+me Here Now,this:place', S::ucwords($input, '- '));
    }

    /**
     * @test
     * @covers ::ucwords
     */
    public function ucwords_noDelimiters()
    {
        $input = 'Mary had a little-lamb';
        $this->assertSame($input, S::ucwords($input, ''));
    }

    /**
     * @test
     * @covers ::ucwords
     */
    public function ucwords_singleDelimiter()
    {
        $input = 'Mary had a little-lamb';
        $this->assertSame('MaRy haD a little-laMb', S::ucwords($input, 'a'));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $string is not a string
     * @covers ::ucwords
     */
    public function ucwords_badTypeString()
    {
        S::ucwords(null);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $delimiters is not a string
     * @covers ::ucwords
     */
    public function ucwords_badTypeDelimiters()
    {
        S::ucwords('test', null);
    }
}

class TestObjectWithToString
{
    public function __toString()
    {
        return 'Jack';
    }
}
