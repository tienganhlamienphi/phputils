<?php

namespace Talmp\Phputils;

class EncodeUtil
{
    # https://www.php.net/manual/en/function.base64-encode.php
    # see in php comment section
    public static function base64EncodeUrl(string $string): string
    {
        return str_replace(['+','/','='], ['-','_',''], base64_encode($string));
    }

    # https://www.php.net/manual/en/function.base64-encode.php
    # see in php comment section
    public static function base64DecodeUrl(string $string): string
    {
        return base64_decode(str_replace(['-','_'], ['+','/'], $string));
    }
}
