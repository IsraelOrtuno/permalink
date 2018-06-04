<?php

namespace Devio\Permalink;

trait HasPermalinks
{
    public static function bootHasPermalinks()
    {
        static::created(function ($model) {
            $model->permalink()->create();
        });

//        static::saved(function ($model) {
//            if (method_exists($model, 'updatePermalinkOnSave') &&
//                call_user_func([$model, 'updatePermalinkOnSave'])) {
//                $model->updatePermalink();
//            }
//        });
    }

    /**
     * Relation to the permalinks table.
     *
     * @return mixed
     */
    public function permalink()
    {
        return $this->morphOne(Permalink::class, 'permalinkable');
    }

    /**
     * Check if the page has a permalink relation.
     *
     * @return bool
     */
    public function hasPermalink()
    {
        return (bool) ! is_null($this->permalink);
    }
}