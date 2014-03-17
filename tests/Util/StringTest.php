<?php

namespace DominionEnterprises\Util;
use DominionEnterprises\Util\String as S;

/**
 * @coversDefaultClass \DominionEnterprises\Util\String
 * @covers ::<private>
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
     * @covers ::ucwords
     */
    public function ucwords_lowercasedInput()
    {
        $this->assertSame(
            'The Quick Brown Fox Jumps Over The Lazy Dog',
            S::ucwords('the quick brown fox jumps over the lazy dog')
        );
    }

    /**
     * @test
     * @covers ::ucwords
     */
    public function ucwords_uppercasedInput()
    {
        $this->assertSame(
            'The Quick Brown Fox Jumps Over The Lazy Dog',
            S::ucwords('THE QUICK BROWN FOX JUMPS OVER THE LAZY DOG')
        );
    }

    /**
     * @test
     * @covers ::ucwords
     */
    public function ucwords()
    {
        $this->assertSame(
            'A-Okay_Yes+No\' Yes:No Good/Bad L,O,L Yes.No',
            S::ucwords('a-okay_yes+no\' yes:no good/bad l,o,l yes.no')
        );
    }

    /**
     * @test
     * @covers ::ucwords
     */
    public function ucwords_withMarkers()
    {
        $this->assertSame('foobar Foo-Bar', S::ucwords('foobar foo-bar', ['\s', '-']));
    }
}

class TestObjectWithToString
{
    public function __toString()
    {
        return 'Jack';
    }
}
