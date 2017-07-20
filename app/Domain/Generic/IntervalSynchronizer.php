<?php

namespace App\Domain\Generic;

class IntervalSynchronizer
{
    protected $interval;

    protected $fn;

    protected $lastRun;

    public function __construct(int $interval, callable $fn)
    {
        $this->interval = $interval;
        $this->fn = $fn;
    }

    public function __invoke(...$args)
    {
        if ($this->lastRun && ($elapsed = time() - $this->lastRun) < $this->interval) {
            sleep($this->interval - $elapsed);
        }

        try {
            return ($this->fn)(...$args);
        } finally {
            $this->lastRun = time();
        }
    }
}
