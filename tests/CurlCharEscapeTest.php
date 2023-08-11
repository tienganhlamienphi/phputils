<?php

use PHPUnit\Framework\TestCase;
use Talmp\Phputils\CurlCharEscape;

class CurlCharEscapeTest extends TestCase
{
    public function test_bash_char_escape(): void
    {
        $this->assertEquals('\\?', CurlCharEscape::escape('?', '\\', '\\\\\\'));

        $this->assertEquals('\\\\\\$', CurlCharEscape::escape('$', '\\', '\\\\\\'));

        // override
        $this->assertEquals(
            'abc',
            CurlCharEscape::escape('"', '\\', '\\\\\\', ['"' => 'abc'])
        );
    }
}
