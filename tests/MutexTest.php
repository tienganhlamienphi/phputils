<?php

use PHPUnit\Framework\TestCase;
use Talmp\Phputils\Mutex;

class MutexTest extends TestCase
{
    public function test_lock_unlock(): void
    {
        $lock_name =  bin2hex(random_bytes(16));

        $this->assertFalse(file_exists('/dev/shm/'.$lock_name));

        $mutex = new Mutex($lock_name);
        $mutex->lock(0);

        $this->assertTrue(file_exists('/dev/shm/'.$lock_name));

        $mutex->unlock();

        $this->assertFalse(file_exists('/dev/shm/'.$lock_name));
    }
}
