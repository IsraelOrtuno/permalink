<?php

namespace Devio\Permalink;

trait HasPermalinks
{
    /**
     * Booting the trait.
     */
    public static function bootHasPermalinks()
    {
        // When an entity has been fully created and stored, we will check if
        // the automatic creation of permalinks is enabled. If so, we will
        // just create the permalink accordingly to the new stored model.
        static::created(function ($model) {
            if ($model->createPermalink ?? true) {
                $model->permalink()->create();
            }
        });
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