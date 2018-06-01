<?php

namespace Devio\Permalink;

trait HasPermalinks
{
    public function permalink()
    {
        return $this->morphOne(Permalink::class, 'permalinkable');
    }
}