<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use UnexpectedValueException;

class Journal extends Model
{
    const LEVELS = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

    protected $table = "journaux";

    protected $fillable = ['level', 'message', 'context'];

    protected $dates = ['date'];

    protected $casts = ['context' => 'array'];

    public static $log = true;

    public $timestamps = false;

    protected static function concieve(string $level,  string $message, array $context = []): self
    {
        if (!in_array($level, self::LEVELS)) {
            throw new UnexpectedValueException("Invalid level: {$level}");
        }

        if (self::$log) {
            Log::{$level}($message, $context);
        }

        return static::create(compact('level', 'message', 'context'));
    }

    public static function debug(string $message, array $context = []): self
    {
        return static::concieve('debug', $message, $context);
    }

    public static function info(string $message, array $context = []): self
    {
        return static::concieve('info', $message, $context);
    }

    public static function notice(string $message, array $context = []): self
    {
        return static::concieve('notice', $message, $context);
    }

    public static function warning(string $message, array $context = []): self
    {
        return static::concieve('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): self
    {
        return static::concieve('error', $message, $context);
    }

    public static function critical(string $message, array $context = []): self
    {
        return static::concieve('critical', $message, $context);
    }

    public static function alert(string $message, array $context = []): self
    {
        return static::concieve('alert', $message, $context);
    }

    public static function emergency(string $message, array $context = []): self
    {
        return static::concieve('emergency', $message, $context);
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
