<?php

use Talmp\Phputils\BashCharEscape;
use PHPUnit\Framework\TestCase;

class BashCharEscapeTest extends TestCase
{
    public function test_bash_char_escape()
    {
        $this->assertEquals('\\\'\\\\\\}\\\'', BashCharEscape::escape('}', '\\', '\\\\\\', "'"));

        // not escape
        $this->assertEquals('{', BashCharEscape::escape('{', '\\', '\\\\\\', "'"));

        $this->assertEquals('\\\'\\\\\\"\\\'', BashCharEscape::escape('"', '\\', '\\\\\\', "'"));

        // override
        $this->assertEquals(
            'abc',
            BashCharEscape::escape('"', '\\', '\\\\\\', "'", ['"' => 'abc'])
        );
    }
}
