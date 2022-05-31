<?php

use PHPUnit\Framework\TestCase;
use Talmp\Phputils\EncodeUtil;

class EncodeUtilTest extends TestCase
{
    public function test_base64_encode_decode_url()
    {
        $string = bin2hex(openssl_random_pseudo_bytes(rand(100, 200)));

        $encoded_str = EncodeUtil::base64EncodeUrl($string);

        $this->assertFalse(str_contains($encoded_str, '+'));
        $this->assertFalse(str_contains($encoded_str, '/'));
        $this->assertFalse(str_contains($encoded_str, '='));

        $decoded_str = EncodeUtil::base64DecodeUrl($encoded_str);

        $this->assertEquals($string, $decoded_str);
    }
}
