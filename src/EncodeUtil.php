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

    public static function gzipFile(
        string $source_path,
        string|null $dest_path = null,
        int $level = 6
    ): string|false {
        if (is_null($dest_path)) {
            $dest_path = $source_path.'.gz';
        }

        $mode = 'wb'.$level;

        $error = false;

        $fp_out = gzopen($dest_path, $mode);

        if ($fp_out === false) {
            return false;
        }

        $fp_in = fopen($source_path, 'rb');

        if ($fp_in === false) {
            return false;
        }

        while (! feof($fp_in)) {
            gzwrite($fp_out, fread($fp_in, 1024 * 512));
        }

        $fclose = fclose($fp_in);

        if ($fclose === false) {
            return false;
        }

        return $dest_path;
    }
}
