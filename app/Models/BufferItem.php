<?php

namespace App\Models;

use App\Domain\Html\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BufferItem extends Model
{
    use SoftDeletes;

    protected $fillable = ['url', 'html'];

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
