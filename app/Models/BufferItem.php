<?php

namespace App\Models;

use App\Domain\Html\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BufferItem extends Model
{
    use SoftDeletes;

    protected $fillable = ['url'];

    public function getDocumentAttribute()
    {
        return Document::fromUrl($this->url);
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
