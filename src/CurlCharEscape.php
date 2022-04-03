<?php

namespace Talmp\Phputils;

class CurlCharEscape
{
    public static function escape(
        string $str,
        string $lbs,
        string $hbs,
        array $over_ride = []
    ): string {
        $kv_replacements = CurlCharEscape::getReplacements([
            'lbs' => $lbs,
            'hbs' => $hbs,
            'over_ride' => $over_ride
        ]);

        $replaced_str = StrUtil::replaceOnce(
            array_keys($kv_replacements),
            array_values($kv_replacements),
            $str
        );

        if ($replaced_str === false) {
            return '';
        }

        return $replaced_str;
    }

    protected static function getReplacements(array $configs): array
    {
        $lbs = $configs['lbs'];
        $hbs = $configs['hbs'];
        $over_ride = $configs['over_ride'];

        return $over_ride + [

            // backslash
            "\\" => "$hbs\\$hbs\\",

            // single quote
            "'" => "$hbs'",

            // double quote
            "\"" => "$hbs\"",

            // back quote
            "`" => "$hbs`",

            // asterisk
            "*" => "$lbs*",

            // left parenthesis
            "(" => "$lbs(",

            // right parenthesis
            ")" => "$lbs)",

            // left square bracket
            "[" => "$lbs"."[",

            // right curly bracket
            "}" => "$lbs}",

            // less than sign
            "<" => "$lbs<",

            // greater than sign
            ">" => "$lbs>",

            // vertical line
            "|" => "$lbs|",

            // semicolon
            ";" => "$lbs;",

            // question mark
            "?" => "$lbs?",

            // equal
            "=" => "$lbs=",

            // pound
            "#" => "$lbs#",

            // ampersand (and)
            "&" => "$lbs&",

            // dollar
            "$" => "$hbs$",
        ];
    }
}
