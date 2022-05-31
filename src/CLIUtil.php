<?php

namespace Talmp\Phputils;

class CLIUtil
{
    public static function killProcessTree(int $pid): bool
    {
        $pstree_output = exec("pstree -p -A $pid", $dummy, $exit_code);

        if ($exit_code !== 0) {
            return false;
        }

        // php(2065)---sh(2069)---php7.3(2070)
        // will matches [2065, 2069, 2070]
        preg_match_all('/(?<=\()\d+(?=\)(?=-|$))/', $pstree_output, $matches);

        foreach ($matches[0] as $match) {
            $kill_result = posix_kill($match, SIGKILL);

            if (! $kill_result) {
                return false;
            }
        }

        $kill_result = posix_kill($pid, SIGKILL);

        if (! $kill_result) {
            return false;
        }

        return true;
    }
}
