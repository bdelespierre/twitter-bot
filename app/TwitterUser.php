<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TwitterUser extends Model
{
    const FOLLOWING = 1;
    const FOLLOWER = 2;
    const MUTED = 4;

    protected $fillable = ['id', 'screen_name', 'mask', 'data'];

    protected $casts = ['data' => 'array'];

    public function scopeFollowing($query)
    {
        return $query->where('mask', '&', self::FOLLOWING);
    }

    public function scopeFollower($query)
    {
        return $query->where('mask', '&', self::FOLLOWER);
    }

    public function scopeMuted($query)
    {
        return $query->where('mask', '&', self::MUTED);
    }

    public function scopeUnmuted($query)
    {
        return $query->where('mask', '&', DB::raw('~' . self::MUTED));
    }

    public function scopeWithScreenName($query, $name = "")
    {
        return $query->where('screen_name', 'like', "%{$name}%");
    }
}
