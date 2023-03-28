<?php

namespace Talmp\Phputils;

use voku\helper\ASCII;

class StrUtil
{
    /**
     * @return array<string>
     */
    public static function toSearchablePhrases(
        string $string,
        int $min_length = 1,
        string $separator = ' ',
        int $limit = PHP_INT_MAX,
    ): array {
        $string = mb_strtolower($string);

        $explode_arr = explode($separator, $string, $limit);

        $result = [];

        // in case input string is not ascii
        // eg: léon
        // and with min_length = 1
        // we will split it into
        // [
        //     'l', 'é', 'e', 'o', 'n',
        //     'lé', 'le', 'éo', 'eo', 'on',
        //     'léo', 'leo', 'éon', 'eon',
        //     'léon', 'leon'
        // ]

        foreach ($explode_arr as $sub_string) {
            $mb_str_split = mb_str_split($sub_string);

            $sub_string_length = count($mb_str_split);
            $pointer = 0;

            while (true) {
                if ($pointer > $sub_string_length - 1) {
                    break;
                }
                
                $length = $min_length;

                while ($pointer + $length < $sub_string_length + 1) {
                    $result[
                        implode('', array_slice($mb_str_split, $pointer, $length))
                    ] = true;

                    $result[
                        static::ascii(
                            implode('', array_slice($mb_str_split, $pointer, $length))
                        )
                    ] = true;

                    $length += 1;
                }

                $pointer += 1;
            }
        }

        return array_keys($result);
    }

    public static function questionMarkToSqlParameterizedPlaceHolder(
        string $string,
        int $limit = 65536 /* default postgres limit */
    ): string|false {
        $multi_strpos = StrUtil::multiStrpos($string, '?');

        $count_multi_strpos = count($multi_strpos);

        if ($count_multi_strpos === 0) {
            return $string;
        }

        if (count($multi_strpos) > $limit) {
            return false;
        }

        $searches = array_fill(0, count($multi_strpos), '?');
        $indexes = [];
        $replacements = [];

        for ($i = 0; $i < $count_multi_strpos; $i++) {
            $indexes[] = [$multi_strpos[$i]];
            $replacements[] = '$'.($i + 1);
        }

        return StrUtil::replaceOnceIndex($searches, $indexes, $replacements, $string);
    }

    /**
     * Transliterate a UTF-8 value to ASCII.
     */
    public static function ascii(string $value, string $language = 'en'): string
    {
        return ASCII::to_ascii($value, $language);
    }

    /**
     * @param array<string> $searches
     * @param array<array<int>> $indexes
     * @param array<string> $replacements
     * @param string $subject
     */
    public static function replaceOnceIndex(
        array $searches,
        array $indexes,
        array $replacements,
        string $subject
    ): string|false {
        if (count($searches) === 0) {
            return false;
        }

        // check searches and indexes match length and correct
        if (count($searches) !== count($indexes)) {
            return false;
        }

        // check if $search at $index is correct
        foreach ($searches as $search_index => $search) {
            if (! is_array($indexes[$search_index])) {
                return false;
            }

            foreach ($indexes[$search_index] as $index) {
                if (mb_substr($subject, $index, mb_strlen($search)) !== $search) {
                    return false;
                }
            }
        }

        if (count($searches) !== count($replacements)) {
            return false;
        }

        // check if indexes and searches overlap
        $overlap_map = array_fill(0, mb_strlen($subject), false);

        foreach ($searches as $search_index => $search) {
            foreach ($indexes[$search_index] as $index) {
                for ($i = $index; $i < $index + mb_strlen($search); $i++) {
                    if (! $overlap_map[$i]) {
                        $overlap_map[$i] = true;
                        continue;
                    }

                    return false;
                }
            }
        }

        // 1. build replace map
        $r_map = [];

        foreach ($searches as $search_index => $search) {
            $r_map[] = $indexes[$search_index];
        }

        // 2. relace by replace map AND update replace map
        // (why update ? sometimes we need replace 'a' with 'bc'
        // notice 'bc' have 2 characters and 'a' only have 1 ? that's why :)

        foreach ($r_map as $r_map_k => $r_map_v) {
            for ($i = 0; $i < count($r_map_v); $i++) {
                $pos = $r_map[$r_map_k][$i];

                $subject =
                     static::replaceAtIndex(
                         $searches[$r_map_k],
                         $replacements[$r_map_k],
                         $subject,
                         $pos
                     );

                $r_map = static::updateReplaceMap(
                    $r_map,
                    $searches,
                    $replacements,
                    $searches[$r_map_k],
                    $replacements[$r_map_k],
                    $pos
                );
            }
        }

        return $subject;
    }

    /**
     * @param array<string> $searchs
     * @param array<string> $replacements
     */
    public static function replaceOnce(
        array $searches,
        array $replacements,
        string $subject
    ): string|false {
        ////////////////////////////////////////////////////////
        // STRATEGY
        ////////////////////////////////////////////////////////
        //
        // 0. check if $searches and $replacements is valid
        //
        // 1. we build our keys index map
        // (or replace map - based on searches/replacements pair)
        //
        // 2. foreach key value replacement
        // we replace not by searching
        // but based on our replace map
        // AND
        // we must update our map accordingly

        // 0. check searches/replacements

        if (count($searches) !== count($replacements)) {
            return false;
        }

        if (count($searches) === 0) {
            return false;
        }

        $pair_count = count($searches);

        $k_character_set = array_unique(mb_str_split($searches[0]));

        for ($i = 0; $i < $pair_count; $i++) {
            if ($i === 0) {
                continue;
            }

            $chars = mb_str_split($searches[$i]);

            if (! empty(array_intersect($k_character_set, $chars))) {
                return false;
            }

            $k_character_set = array_merge($k_character_set, array_unique($chars));
        }

        // 1. build replace replace map

        $r_map = [];

        foreach ($searches as $search) {
            $r_map[] = static::multiStrpos($subject, $search);
        }

        // 2. relace by replace map AND update replace map
        // (why update ? sometimes we need replace 'a' with 'bc'
        // notice 'bc' have 2 characters and 'a' only have 1 ? that's why :)

        foreach ($r_map as $r_map_k => $r_map_v) {
            for ($i = 0; $i < count($r_map_v); $i++) {
                $pos = $r_map[$r_map_k][$i];

                $subject =
                     static::replaceAtIndex(
                         $searches[$r_map_k],
                         $replacements[$r_map_k],
                         $subject,
                         $pos
                     );

                $r_map = static::updateReplaceMap(
                    $r_map,
                    $searches,
                    $replacements,
                    $searches[$r_map_k],
                    $replacements[$r_map_k],
                    $pos
                );
            }
        }

        return $subject;
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return array<int, int<0, max>>
     */
    protected static function multiStrpos(string $haystack, string $needle): array
    {
        // https://gist.github.com/vielhuber/de9542d5fead3b3709c60b13e1350a92

        $positions = [];

        $pos_last = 0;

        while (($pos_last = mb_strpos($haystack, $needle, $pos_last)) !== false) {
            $positions[] = $pos_last;

            $pos_last = $pos_last + mb_strlen($needle);
        }

        return $positions;
    }

    protected static function replaceAtIndex(
        string $search,
        string $replace,
        string $subject,
        int $index
    ): string {

        // this function behave more like pointer than actual replace function

        // eg:
        // $search = 'abc'
        // $replace = 'defh'
        // $subject = 'abcd abcd abcd'
        // replaceAtIndex($search, $replace, $subject, 0) => 'defhd abcd abcd'
        // replaceAtIndex($search, $replace, $subject, 5) => 'abcd defhd abcd'
        // replaceAtIndex($search, $replace, $subject, 1) // it wrong, tripple think it boiss

        return
            mb_substr($subject, 0, $index).
            $replace.
            mb_substr($subject, mb_strlen($search) + $index);
    }

    /**
     *
     */
    protected static function updateReplaceMap(
        array $replace_map,
        array $searches,
        array $replacements,
        string $search,
        string $replace,
        int $index
    ): array {

        // this too is a pointer function on multi dimensional array

        // ex:
        // we have a string 'aacffade'
        // we want to replace
        // 'f' -> 'ab'
        // 'a' -> 'ff'

        // the first time when we build our replace_map it gonna look like
        //
        // replace_map        searches        |    replacements
        // [             |    [               |    [
        //   '0' => [    |      '0' => 'f',   |      '0' => 'ab',
        //     0 => 3,   |                    |
        //     1 => 4    |                    |
        //   ],          |                    |
        //   '1' => [    |      '1' -> 'a'    |      '1' => 'ff'
        //     0 => 0,   |                    |
        //     1 => 1,   |                    |
        //     2 => 5    |                    |
        //   ]           |                    |
        // ]             |    ]               |    ]

        // when we replace the first character
        // that is the first 'f' => 'ab' (only the first f)
        // replace_map should look like
        // [
        //   '0' => [
        //     0 => 3
        //     1 => 5 // 4 + 2 - 1 ( old index + 'ab' length - 'f' length)
        //   ],
        //   '0' => [
        //     0 => 0, // keep the same as smaller then 3 (our first f index)
        //     1 => 1, // keep same
        //     2 => 6, // (old index + 'ab' length - 'f' length)
        //   ]
        // ]

        // delete key

        $search_idx = array_search($search, $searches, true);

        foreach ($replace_map[$search_idx] as $r_map_v_k => $r_map_v_v) {
            if ($index === $r_map_v_v) {
                unset($replace_map[$search_idx][$r_map_v_k]);

                break;
            }
        }

        foreach ($replace_map[$search_idx] as $r_map_k => $r_map_v) {
            $replace_map[$search_idx][$r_map_k] =
                $r_map_v + (+ mb_strlen($replace) - mb_strlen($search));
        }

        foreach ($replace_map as $r_map_k => $r_map_v) {
            if ($r_map_k === $search_idx) {
                continue;
            }

            foreach ($r_map_v as $r_map_v_k => $r_map_v_v) {
                if ($r_map_v_v < $index) {
                    continue;
                }

                $replace_map[$r_map_k][$r_map_v_k] += (+ mb_strlen($replace) - mb_strlen($search));
            }
        }

        return $replace_map;
    }
}
