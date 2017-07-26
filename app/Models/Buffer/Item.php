<?php

namespace App\Models\Buffer;

use App\Domain\Html\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $table = "buffer_items";

    protected $fillable = ['url', 'html'];

    public static function pickOneAtRandom()
    {
        return self::orderBy(DB::raw('RANDOM()'))->first();
    }

    public function getDocumentAttribute()
    {
        if ($this->html) {
            return Document::fromHTML($this->html);
        }

        $this->update([
            'html' => (string) ($doc = Document::fromUrl($this->url))
        ]);

        return $doc;
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
