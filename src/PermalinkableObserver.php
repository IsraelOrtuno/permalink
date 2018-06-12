<?php

namespace Devio\Permalink;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class PermalinkableObserver
{
    /**
     * Saved model event handler.
     *
     * @param Model $model
     */
    public function saved(Model $model)
    {
        if (! $this->managePermalinks($model)) {
            return;
        }

        $attributes = $this->gatherAttributes($model->getPermalinkAttributes());

        $model->storePermalink($attributes);
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
        $attributes = $attributes ?: request();

        return ($attributes instanceof Request ? $attributes->get('permalink') : $attributes) ?? [];
    }
}