<?php

namespace App\Models\Twitter;

use App\Domain\Generic\Str;
use App\Support\Facades\Language;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Thujohn\Twitter\Facades\Twitter;

class User extends Model
{
    const FRIEND   =  1;
    const FOLLOWER =  2;
    const MUTED    =  4;
    const BLOCKED  =  8;
    const VIP      = 16;

    protected $table = 'twitter_users';

    protected $fillable = ['id', 'screen_name', 'mask', 'data'];

    protected $casts = ['data' => 'array'];

    public function __toString()
    {
        return (string) view('components.twitter_profile', ['id' => $this->id] + $this->data);
    }

    public function scopeWithScreenName($query, $name = "")
    {
        return $query->where('screen_name', 'like', "%{$name}%");
    }

    public function updateAttributes()
    {
        $this->setFriendAttribute(array_get($this->data, 'following', false));
        $this->setMutedAttribute(array_get($this->data, 'muting', false));
        $this->setBlockedAttribute(array_get($this->data, 'blocking', false));

        return $this;
    }

    public function getNameAttribute()
    {
        return Str::decodeUnicode(array_get($this->data, 'name'));
    }

    public function getDescriptionAttribute()
    {
        return Str::decodeUnicode(array_get($this->data, 'description'));
    }

    public function getLangAttribute()
    {
        if (Cache::has($key = "app.models.twitter.user:{$this->id}.lang")) {
            return Cache::get($key);
        }

        $lang = Language::simpleDetect("{$this->name} {$this->description}");

        $expiresAt = Carbon::now()->addDays(3);
        Cache::put($key, $lang, $expiresAt);

        return $lang;
    }

    /*
    |--------------------------------------------------------------------------
    | Friend
    |--------------------------------------------------------------------------
    |
    */

    public function scopeFriends($query)
    {
        return $query->whereRaw('mask &' . self::FRIEND . ' != 0');
    }

    public function scopeExceptFriends($query)
    {
        return $query->whereRaw('mask &' . self::FRIEND . '  = 0');
    }

    public function scopeFans($query)
    {
        return $query->followers()->exceptFriends();
    }

    public function isFriend()
    {
        return (boolean) $this->mask & self::FRIEND;
    }

    public function getFriendAttribute()
    {
        return $this->isFriend();
    }

    public function setFriendAttribute($friend)
    {
        $friend
            ? $this->mask |=  self::FRIEND
            : $this->mask &= ~self::FRIEND;

        return $this;
    }

    public function follow()
    {
        Twitter::postFollow(['user_id' => $this->id]);
        $this->setFriendAttribute(true)->save();

        return $this;
    }

    public function unfollow()
    {
        Twitter::postUnfollow(['user_id' => $this->id]);
        $this->setFriendAttribute(false)->save();

        return $this;
    }

    public static function getFriendsIds()
    {
        if (!Cache::has($key = 'app.models.twitter.user:friends_ids')) {
            $friendsIds = array_get(Twitter::getFriendsIds(['format' => 'array']), 'ids', []);
            $expiresAt  = Carbon::now()->addMinutes(15);

            Cache::put($key, $friendsIds, $expiresAt);
        }

        return Cache::get($key);
    }

    /*
    |--------------------------------------------------------------------------
    | Follower
    |--------------------------------------------------------------------------
    |
    */

    public function scopeFollowers($query)
    {
        return $query->whereRaw('mask &' . self::FOLLOWER . ' != 0');
    }

    public function scopeExceptFollower($query)
    {
        return $query->whereRaw('mask &' . self::FOLLOWER . '  = 0');
    }

    public function isFollower()
    {
        return (boolean) $this->mask & self::FOLLOWER;
    }

    public function getFollowerAttribute()
    {
        return $this->isFollower();
    }

    public function setFollowerAttribute($follower)
    {
        $follower
            ? $this->mask |=  self::FOLLOWER
            : $this->mask &= ~self::FOLLOWER;

        return $this;
    }

    public static function getFollowersIds()
    {
        if (!Cache::has($key = 'app.models.twitter.user:followers_ids')) {
            $followersIds = array_get(Twitter::getFollowersIds(['format' => 'array']), 'ids', []);
            $expiresAt    = Carbon::now()->addMinutes(15);

            Cache::put($key, $followersIds, $expiresAt);
        }

        return Cache::get($key);
    }

    /*
    |--------------------------------------------------------------------------
    | Muted
    |--------------------------------------------------------------------------
    |
    */

    public function scopeMuted($query)
    {
        return $query->whereRaw('mask &' . self::MUTED . ' != 0');
    }

    public function scopeExceptMuted($query)
    {
        return $query->whereRaw('mask &' . self::MUTED . '  = 0');
    }

    public function isMuted()
    {
        return (boolean) $this->mask & self::MUTED;
    }

    public function getMutedAttribute()
    {
        return $this->isMuted();
    }

    public function setMutedAttribute($muted)
    {
        $muted
            ? $this->mask |=  self::MUTED
            : $this->mask &= ~self::MUTED;

        return $this;
    }

    public function mute()
    {
        Twitter::muteUser(['user_id' => $this->id]);
        $this->setMutedAttribute(true)->save();

        return $this;
    }

    public function unmute()
    {
        Twitter::unmuteUser(['user_id' => $this->id]);
        $this->setMutedAttribute(false)->save();

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Blocked
    |--------------------------------------------------------------------------
    |
    */

    public function block()
    {
        Twitter::postBlock(['user_id' => $this->id]);
        $this->setBlockedAttribute(true)->save();

        return $this;
    }

    public function scopeBlocked($query)
    {
        return $query->whereRaw('mask &' . self::BLOCKED . ' != 0');
    }

    public function scopeExceptBlocked($query)
    {
        return $query->whereRaw('mask &' . self::BLOCKED . '  = 0');
    }

    public function isBlocked()
    {
        return (boolean) $this->mask & self::BLOCKED;
    }

    public function getBlockedAttribute()
    {
        return $this->isBlocked();
    }

    public function setBlockedAttribute($blocked)
    {
        $blocked
            ? $this->mask |=  self::BLOCKED
            : $this->mask &= ~self::BLOCKED;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | VIP
    |--------------------------------------------------------------------------
    |
    */

    public function scopeVip($query)
    {
        return $query->whereRaw('mask &' . self::VIP . ' != 0');
    }

    public function scopeExceptVip($query)
    {
        return $query->whereRaw('mask &' . self::VIP . '  = 0');
    }

    public function isVip()
    {
        return (boolean) $this->mask & self::VIP;
    }

    public function getVipAttribute()
    {
        return $this->isVip();
    }

    public function setVipAttribute($vip)
    {
        $vip
            ? $this->mask |=  self::VIP
            : $this->mask &= ~self::VIP;

        return $this;
    }
}
