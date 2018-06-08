<?php

namespace Devio\Permalink;

use Devio\Permalink\Contracts\Permalinkable;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class PermalinkableObserver
{
    /**
     * Created model event handler.
     *
     * @param Model $model
     */
    public function created(Model $model)
    {
        if (! $this->managePermalinks($model)) {
            return;
        }

        $model->permalink()->create($this->gatherAttributes($model));
    }

    /**
     * Updated model event handler.
     *
     * @param Model $model
     */
    public function updated(Model $model)
    {
        if (! $this->managePermalinks($model) || ! $model->permalink) {
            return;
        }

        $model->permalink()->update($this->gatherAttributes($model));
    }

    /**
     * Check if the model should auto manage the permalinks.
     *
     * @param Model $model
     * @return bool|mixed
     */
    protected function managePermalinks(Model $model)
    {
        return $model->managePermalinks ?? true;
    }

    /**
     * Get the attributes from the request or 'permalink' key.
     *
     * @param null $attributes
     * @return array
     */
    protected function gatherAttributes($attributes = null)
    {
        if ($attributes instanceof Permalinkable) {
            return $attributes->getPermalinkAttributes();
        }

        $attributes = $attributes ?: request();

        return ($attributes instanceof Request ? $attributes->get('permalink') : $attributes) ?? [];
    }
}