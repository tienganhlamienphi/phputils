<?php

namespace Talmp\Phputils;

class BashCharEscape
{
    public static function escape(
        string $str,
        string $lbs,
        string $hbs,
        string $quote = "'",
        array $over_ride = []
    ): string {
        $kv_replacements = BashCharEscape::getReplacements([
            'lbs' => $lbs,
            'hbs' => $hbs,
            'quote' => $quote,
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
        $quote = $configs['quote'];
        $over_ride = $configs['over_ride'];

        return $over_ride + [

            // single quote
            "'" => "$lbs$quote$hbs'$lbs$quote",

            // double quote
            "\"" => "$lbs$quote$hbs\"$lbs$quote",

            // back quote
            "`" => "$lbs$quote$hbs`$lbs$quote",

            // backslash
            "\\" => "$lbs$quote$hbs\\$lbs$quote",

            // asterisk
            "*" => "$lbs$quote$hbs*$lbs$quote",

            // left parenthesis
            "(" => "$lbs$quote$hbs($lbs$quote",

            // right parenthesis
            ")" => "$lbs$quote$hbs)$lbs$quote",

            // left square bracket
            "[" => "$lbs$quote$hbs"."["."$lbs$quote",

            // right curly bracket
            "}" => "$lbs$quote$hbs"."}"."$lbs$quote",

            // less than sign
            "<" => "$lbs$quote$hbs"."<"."$lbs$quote",

            // greater than sign
            ">" => "$lbs$quote$hbs".">"."$lbs$quote",

            // vertical line
            "|" => "$lbs$quote$hbs|$lbs$quote",

            // space
            " " => "$lbs$quote$hbs $lbs$quote",

            // semicolon
            ";" => "$lbs$quote$hbs;$lbs$quote",

            // question mark
            "?" => "$lbs$quote$hbs"."?"."$lbs$quote",

            // ampersand (and)
            "&" => "$lbs$quote$hbs&$lbs$quote",

            // minus
            "-" => "$lbs$quote$hbs-$lbs$quote",

            // dollar sign
            "$" => "$lbs$quote$hbs$$lbs$quote",

            // exclamation mark, bang
            "!" => "$lbs$quote$hbs!$lbs$quote",

            // caret, circumflex
            "^" => "$lbs$quote$hbs^$lbs$quote"

        ];
    }
}
