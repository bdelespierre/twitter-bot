<?php

namespace App\Models\Buffer;

use App\Domain\Html\Document;
use App\Models\HasDocument;
use App\Models\Pool\Item as PoolItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    use SoftDeletes, HasDocument;

    protected $table = "buffer_items";

    protected $fillable = ['url', 'html'];

    protected $dates = ['deleted_at'];

    public function __toString()
    {
        return (string) view('buffer._item', ['item' => $this]);
    }

    public static function fromPool(PoolItem $item): self
    {
        return static::create([
            'url'  => $item->url,
            'html' => $item->html,
        ]);
    }

    public static function pickOneAtRandom()
    {
        return self::orderBy(DB::raw('RANDOM()'))->first();
    }

    public function scopeWithUrl($query, $search)
    {
        $search = strtolower($search);
        return $query->where('url', 'like', "%{$search}%");
    }

    public function tweet()
    {
        return Artisan::call('twitter:tweet', ['item' => $this]);
    }
}
