<?php

namespace Talmp\Phputils;

class ProcessUtil
{
    public static function killTree(int $pid): void
    {
        $pstree_output = exec("pstree -p -A $pid");

        // php(2065)---sh(2069)---php7.3(2070)
        // will matches [2065, 2069, 2070]
        preg_match_all('/(?<=\()\d+(?=\)(?=-|$))/', $pstree_output, $matches);

        foreach ($matches[0] as $match) {
            posix_kill($match, SIGKILL);
        }
    }
}
