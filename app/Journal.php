<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Journal extends Model
{
    const LEVELS = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

    protected $table = "journaux";

    protected $fillable = ['level', 'message', 'context'];

    protected $dates = ['date'];

    protected $casts = ['context' => 'array'];

    public $timestamps = false;

    public static function debug(string $message, array $context = []): self
    {
        Log::debug($message, $context);
        return static::create(['level' => 'debug'] + compact('message', 'context'));
    }

    public static function info(string $message, array $context = []): self
    {
        Log::info($message, $context);
        return static::create(['level' => 'info'] + compact('message', 'context'));
    }

    public static function notice(string $message, array $context = []): self
    {
        Log::notice($message, $context);
        return static::create(['level' => 'notice'] + compact('message', 'context'));
    }

    public static function warning(string $message, array $context = []): self
    {
        Log::warning($message, $context);
        return static::create(['level' => 'warning'] + compact('message', 'context'));
    }

    public static function error(string $message, array $context = []): self
    {
        Log::error($message, $context);
        return static::create(['level' => 'error'] + compact('message', 'context'));
    }

    public static function critical(string $message, array $context = []): self
    {
        Log::critical($message, $context);
        return static::create(['level' => 'critical'] + compact('message', 'context'));
    }

    public static function alert(string $message, array $context = []): self
    {
        Log::alert($message, $context);
        return static::create(['level' => 'alert'] + compact('message', 'context'));
    }

    public static function emergency(string $message, array $context = []): self
    {
        Log::emergency($message, $context);
        return static::create(['level' => 'emergency'] + compact('message', 'context'));
    }

    public function getCssAttribute()
    {
        switch ($this->level) {
            case 'debug':
            case 'info':
                return "text-muted";

            case 'notice':
                return "";

            case 'warning':
                return "text-warning";

            case 'error':
                return "text-danger";

            case 'critical':
                return "bg-warning";

            case 'alert':
                return "bg-danger";

            case 'emergency':
                return "bg-danger text-bold";

            default:
                return "";
        }
    }

    public function getNamespaceAttribute()
    {
        if (preg_match('/^\[([^\]]+)\]/', $this->message, $matches)) {
            return $matches[1];
        }

        return "n/a";
    }

    public function __toString()
    {

    }
}
