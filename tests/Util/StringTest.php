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
        $this->assertSame($input, S::ellipsize($input, -1));
        $this->assertSame($input, S::ellipsize($input, 0));
        $this->assertSame($input, S::ellipsize($input, 3));
        $this->assertSame('S...', S::ellipsize($input, 4));
        $this->assertSame('Short text...', S::ellipsize($input, 13));
        $this->assertSame($input, S::ellipsize($input, 50));
    }

    /**
     * @test
     * @covers ::ellipsize
     */
    public function ellipsize_override()
    {
        $this->assertSame('Test!', S::ellipsize('Testing', 5, '!'));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $string is not a string
     * @covers ::ellipsize
     */
    public function ellipsize_badTypeString()
    {
        S::ellipsize(null, 10);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $maxLength is not an integer
     * @covers ::ellipsize
     */
    public function ellipsize_badTypeMaxLength()
    {
        S::ellipsize('test', 'a');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $suffix is not a string
     * @covers ::ellipsize
     */
    public function ellipsize_badTypeSuffix()
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
    public function ucwords_optionalMarkers()
    {
        $input = 'break-down o\'boy up_town you+me here now,this:place';
        $markers = array('\A', '-', '\s');
        $this->assertSame('Break-Down O\'boy Up_town You+me Here Now,this:place', S::ucwords($input, $markers));
    }

    /**
     * @test
     * @covers ::ucwords
     */
    public function ucwords_simpleMarkers()
    {
        $input = 'Marry had a little-lamb';
        $markers = array('a', '-', 'l');
        $this->assertSame('MaRry haD a lIttlE-laMb', S::ucwords($input, $markers));
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
     * @expectedExceptionMessage $markers is not an array
     * @covers ::ucwords
     */
    public function ucwords_badTypeMarkers()
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
