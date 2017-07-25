<?php

namespace App\Domain\Twitter;

use App\Domain\Generic\IntervalSynchronizer;
use Carbon\Carbon;
use Generator;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use IteratorAggregate;
use Thujohn\Twitter\Facades\Twitter;

class CursoredCollection implements IteratorAggregate
{
    protected $endpoint;

    protected $key;

    protected $options;

    public function __construct(string $endpoint, string $key, array $options = [])
    {
        if (isset($options['cache']) && !is_array($options['cache'])) {
            throw new InvalidArgumentException('Cache options is expected to be array');
        }

        if (isset($options['cache']) && !isset($options['cache']['key'])) {
            throw new InvalidArgumentException('You must provide a cache key');
        }

        if (isset($options['tap']) && !is_callable($options['tap'])) {
            throw new InvalidArgumentException('Tap function is expected to be callable');
        }

        $this->endpoint = $endpoint;
        $this->key      = $key;
        $this->options  = $options;
    }

    protected function option($name, $default = null)
    {
        return array_get($this->options, $name, $default);
    }

    public function getIterator(): Generator
    {
        $format = 'array';

        if ($this->option('cursor')) {
            $cursor = $this->option('cursor');
        }

        if (!isset($cursor) && $this->option('cache') && Cache::has($this->option('cache.key'))) {
            $cursor = Cache::get($this->option('cache.key'));
        }

        $fetchCollection = function ($params) {
            return Twitter::{$this->endpoint}($params + $this->option('args', []));
        };

        if ($this->option('throttle')) {
            $fetchCollection = new IntervalSynchronizer($this->option('throttle'), $fetchCollection);
        }

        do {
            $collection = $fetchCollection(compact('format', 'cursor'));
            $cursor = $collection['next_cursor_str'] ?? "";

            foreach (array_get($collection, $this->key, []) as $item) {
                yield $item;
            }

            if ($cursor && $this->option('cache')) {
                $expireAt = Carbon::now()->addMinutes($this->option('cache.ttl', 180)); // 3h
                Cache::put($this->option('cache.key'), $cursor, $expireAt);
            }

            if ($this->option('tap')) {
                $this->option('tap')($collection, $cursor);
            }
        } while ($cursor);
    }
}
