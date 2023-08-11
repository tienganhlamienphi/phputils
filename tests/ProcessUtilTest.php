<?php

use PHPUnit\Framework\TestCase;
use Talmp\Phputils\ProcessUtil;

class ProcessUtilTest extends TestCase
{
    public function test_kill_tree(): void
    {
        $process = proc_open(
            'php -r "exec(\'sleep 60\');"',
            [STDIN, STDOUT, STDOUT],
            $pipes
        );

        $process_status = proc_get_status($process);

        $this->assertTrue(file_exists('/proc/'.$process_status['pid']));

        exec('kill -0 '.$process_status['pid'], $output, $exit_code);

        $this->assertEquals(0, $exit_code);

        $this->assertNotFalse(posix_getpgid($process_status['pid']));

        $time_start = time();

        ProcessUtil::killTree($process_status['pid']);

        while (true) {
            $process_status = proc_get_status($process);

            if ($process_status['running'] === false) {
                break;
            }
        }

        $this->assertFalse($process_status['running']);
        $this->assertEquals(-1, $process_status['exitcode']);

        $time_end = time();

        $this->assertTrue($time_end - $time_start < 60);
    }
}
