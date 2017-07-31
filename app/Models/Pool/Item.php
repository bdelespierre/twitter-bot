<?php

namespace App\Models\Pool;

use App\Domain\Feed\Item as FeedItem;
use App\Models\HasDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes, HasDocument;

    protected $table = "pool_items";

    protected $fillable = ['url', 'title', 'description', 'date', 'html'];

    protected $dates = ['date', 'deleted_at'];

    public function __toString()
    {
        return (string) view('buffer._item', ['item' => $this]);
    }

    public static function fromFeed(FeedItem $item): self
    {
        return static::create([
            'url'         => $item->link,
            'title'       => $item->title,
            'description' => $item->description,
            'date'        => $item->date,
            'html'        => (string) $item->document,
        ]);
    }
}
