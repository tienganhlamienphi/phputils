<?php

namespace Talmp\Phputils;

class StrUtil
{
    public static function replaceOnce(
        array $searches,
        array $replacements,
        string $str
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

        if (count($searches) == 0) {
            return false;
        }

        $pair_count = count($searches);

        $k_character_set = array_unique(str_split($searches[0]));

        for ($i = 0; $i < $pair_count; $i++) {
            if ($i === 0) {
                continue;
            }

            $chars = str_split($searches[$i]);

            if (! empty(array_intersect($k_character_set, $chars))) {
                return false;
            }

            $k_character_set = array_merge($k_character_set, array_unique($chars));
        }

        // 1. build replace replace map

        $r_map = [];

        foreach ($searches as $search) {
            $r_map[] = static::multiStrpos($str, $search);
        }

        // 2. relace by replace map AND update replace map
        // (why update ? sometimes we need replace 'a' with 'bc'
        // notice 'bc' have 2 characters and 'a' only have 1 ? that's why :)

        foreach ($r_map as $r_map_k => $r_map_v) {
            for ($i = 0; $i < count($r_map_v); $i++) {
                $pos = $r_map[$r_map_k][$i];

                $str =
                     static::replaceAtIndex(
                         $searches[$r_map_k],
                         $replacements[$r_map_k],
                         $str,
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

        return $str;
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

        while (($pos_last = strpos($haystack, $needle, $pos_last)) !== false) {
            $positions[] = $pos_last;

            $pos_last = $pos_last + strlen($needle);
        }

        return $positions;
    }

    protected static function replaceAtIndex(
        string $search,
        string $replace,
        string $str,
        int $index
    ): string {

        // this function behave more like pointer than actual replace function

        // eg:
        // $search = 'abc'
        // $replace = 'defh'
        // $str = 'abcd abcd abcd'
        // replaceAtIndex($search, $replace, $str, 0) => 'defhd abcd abcd'
        // replaceAtIndex($search, $replace, $str, 5) => 'abcd defhd abcd'
        // replaceAtIndex($search, $replace, $str, 1) // it wrong, tripple think it boiss

        return
            substr($str, 0, $index).
            $replace.
            substr($str, strlen($search) + $index);
    }

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
                $r_map_v + (+ strlen($replace) - strlen($search));
        }

        foreach ($replace_map as $r_map_k => $r_map_v) {
            if ($r_map_k === $search_idx) {
                continue;
            }

            foreach ($r_map_v as $r_map_v_k => $r_map_v_v) {
                if ($r_map_v_v < $index) {
                    continue;
                }

                $replace_map[$r_map_k][$r_map_v_k] += (+ strlen($replace) - strlen($search));
            }
        }

        return $replace_map;
    }
}
