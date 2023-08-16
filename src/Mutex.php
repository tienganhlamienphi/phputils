<?php

namespace Talmp\Phputils;

# This is an mutex implementation at OS level
class Mutex
{
    public readonly string $lock_name;

    public function __construct(string $lock_name)
    {
        $this->lock_name = $lock_name;
    }

    public function lock(
        float $timeout = 0 /* microseconds */
    ): bool {
        $timepass = 0;

        while ($timepass <= $timeout) {
            $mkdir = @mkdir("/dev/shm/{$this->lock_name}");

            if ($mkdir) {
                return true;
            }

            $timepass += 100000;

            usleep(100000); // check every 0.1s
        }

        // throw exception here because lock was expected to success
        // client api should not have to check and handle this
        throw new \Exception('PU2991: unable to get lock');
    }

    public function unlock(): void
    {
        $rmdir = rmdir("/dev/shm/{$this->lock_name}");

        if (! $rmdir) {
            throw new \Exception('PU2992: unable unlock');
        }
    }
}
