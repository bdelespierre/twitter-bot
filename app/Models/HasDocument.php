<?php

namespace App\Models;

use App\Domain\Html\Document;
use Carbon\Carbon;

trait HasDocument
{
    protected $urlKey = "url";

    protected $documentKey = "html";

    public function refresh()
    {
        $this[$this->documentKey] = (string) Document::fromUrl($this[$this->urlKey]);

        if ($this->timestamps) {
            $this->updated_at = Carbon::now();
        }

        $this->save();
        return $this;
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