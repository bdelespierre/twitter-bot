<?php

namespace App\Models\Buffer;

use App\Domain\Html\Document;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $table = "buffer_items";

    protected $fillable = ['url', 'html'];

    protected $dates = ['deleted_at'];

    public function __toString()
    {
        return (string) view('buffer._item', ['item' => $this]);
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

    public function refresh()
    {
        $this->html = (string) ($doc = Document::fromUrl($this->url));
        $this->updated_at = Carbon::now();
        $this->save();

        return $this;
    }

    public function tweet()
    {
        return Artisan::call('twitter:tweet', ['item' => $this]);
    }

    public function getDocumentAttribute()
    {
        return $this->html
            ? Document::fromHTML($this->html)
            : $this->refresh()->getDocumentAttribute();
    }

    public function getArticleAttribute()
    {
        return $this->document->article;
    }

    public function getArticlesAttributes()
    {
        return $this->document->articles;
    }

    public function getMetadataAttribute()
    {
        return $this->document->metadata;
    }

    public function getCardAttribute()
    {
        return $this->metadata->card;
    }
}
