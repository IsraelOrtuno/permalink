<?php

namespace Devio\Permalink;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class EntityObserver
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
     * Deleted model event handler.
     *
     * @param Model $model
     */
    public function deleted(Model $model)
    {
        // The permalink should be deleted if the main entity gets destroyed as
        // it won't be able to find the related resource. Not deleting would
        // cause problems when pre-loading the permalink route collection.
        $model->permalink->delete();
    }

    /**
     * Check if the model should auto manage the permalinks.
     *
     * @param Model $model
     * @return bool
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