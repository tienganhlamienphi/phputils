<?php

use PHPUnit\Framework\TestCase;
use Talmp\Phputils\StrUtil;

class StrUtilTest extends TestCase
{
    public function test_to_searchable_phrases()
    {
        $this->assertEquals(
            StrUtil::toSearchablePhrases('léon'),
            StrUtil::toSearchablePhrases('Léon')
        );

        $this->assertEquals(16, count(StrUtil::toSearchablePhrases('léon')));

        $this->assertEquals(16, count(StrUtil::toSearchablePhrases('léon')));

        $this->assertEquals(
            StrUtil::toSearchablePhrases('amélie'),
            StrUtil::toSearchablePhrases('AméLie')
        );

        $this->assertEquals(32, count(StrUtil::toSearchablePhrases('amélie')));

        $this->assertEquals(
            StrUtil::toSearchablePhrases('amélie', 4),
            [
                "amél",
                "amel",
                "améli",
                "ameli",
                "amélie",
                "amelie",
                "méli",
                "meli",
                "mélie",
                "melie",
                "élie",
                "elie",
            ]
        );
    }

    public function test_replace_once()
    {
        // case 0
        // characters between key must be distinct
        // 12 and 23 have 2 in common
        $this->assertFalse(StrUtil::replaceOnce([12, 23], [9, 88], '123'));

        // case 1
        $this->assertEquals('23', StrUtil::replaceOnce([1, 2], [2, 3], '12'));
        $this->assertEquals('23', StrUtil::replaceOnce([2, 1], [3, 2], '12'));
        $this->assertEquals('bccc', StrUtil::replaceOnce(['a', 'b'], ['b', 'c'], 'abbc'));
        $this->assertEquals('233', StrUtil::replaceOnce([1, 2], [2, 3], '123'));

        // case 2
        $this->assertEquals(
            'aabccde',
             StrUtil::replaceOnce(['a', 'b', 'f'], ['b', 'c', 'a'], 'ffabcde')
        );

        // case 3
        $this->assertEquals(
            '"aaaccde',
             StrUtil::replaceOnce(["'", 'b', 'f'], ['"', 'c', 'a'], '\'ffabcde')
        );

        // case 4
        $this->assertEquals('axxbyyc', StrUtil::replaceOnce(['?', ';'], ['xx', 'yy'], 'a?b;c'));
        $this->assertEquals('ayybxxc', StrUtil::replaceOnce(['?', ';'], ['xx', 'yy'], 'a;b?c'));

        // case 5
        $this->assertEquals(
            'a1b0c',
             StrUtil::replaceOnce(['0', '1'], ['0', '1'], 'a1b0c')
        );

        $this->assertEquals(
            'ax1bx0c',
             StrUtil::replaceOnce(['0', '1'], ['x0', 'x1'], 'a1b0c')
        );

        $this->assertEquals(
            'ax10x2bx010c',
             StrUtil::replaceOnce(["0", "1"], ["x010", "x10x2"], 'a1b0c'),
        );

        $this->assertEquals(
             StrUtil::replaceOnce(['1', '0'], ['x110x2', 'x010'], 'a1b0c'), 'ax110x2bx010c'
        );

        // case n
        $this->assertEquals(
            'a277177',
             StrUtil::replaceOnce(["02", "13"], ["7", "7"], 'a20271137'),
        );

        // case 6
        $this->assertEquals(
            'a\';\'?c',
             StrUtil::replaceOnce(["?", ";"], ['\'?', '\';'], 'a;?c')
        );

        $this->assertEquals(
            'a\';\'?c',
             StrUtil::replaceOnce([';', '?'], ['\';', '\'?'], 'a;?c')
        );

        // case 7
        $this->assertEquals(
            'bcbc',
             StrUtil::replaceOnce(['a'], ['bc'], 'aa')
        );

        // case 8
        $this->assertEquals(
            'a1\'\ \'\'\ \'bx010c',
             StrUtil::replaceOnce([' ', 'x'], ['\'\ \'', 'x010'], 'a1  bxc')
        );

        $this->assertEquals(
            'a1\'\ \'\'\ \'bx010c',
             StrUtil::replaceOnce([' ', '0'], ['\'\ \'', 'x010'], 'a1  b0c')
        );
    }

    public function test_replace_once_index()
    {
        // case 0
        // empty search
        $this->assertFalse(
            StrUtil::replaceOnceIndex([], [], ['str_replace'], "replace replace with str_replace")
        );

        // case 1
        // searches count not equals indexes count
        $this->assertFalse(
            StrUtil::replaceOnceIndex(
                ['replace'],
                [],
                ['str_replace'],
                "replace replace with str_replace"
            )
        );

        // case 2
        // index element must be array
        $this->assertFalse(
            StrUtil::replaceOnceIndex(
                ['replace'],
                [7],
                ['str_replace'],
                "replace replace with str_replace"
            )
        );

        // case 3
        // search at index is not correct
        $this->assertFalse(
            StrUtil::replaceOnceIndex(
                ['replace'],
                [[7]],
                ['str_replace'],
                "replace replace with str_replace"
            )
        );

        // case 4
        // searches count not equals replacements count
        $this->assertFalse(
            StrUtil::replaceOnceIndex(
                ['replace'],
                [[8]],
                ['str_replace', 'test'],
                "replace replace with str_replace"
            )
        );

        // case 5
        // indexes and searches overlap
        $this->assertFalse(
            StrUtil::replaceOnceIndex(
                ['replace', 'lace'],
                [[8], [11]],
                ['str_replace', 'test'],
                "replace replace with str_replace"
            )
        );

        // case 6
        // correct case
        $this->assertEquals(
            "replace str_replace with str_replace",
            StrUtil::replaceOnceIndex(
                ['replace'],
                [[8]],
                ['str_replace'],
                "replace replace with str_replace"
            )
        );
    }
}
