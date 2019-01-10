<?php

namespace Devio\Permalink;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class EntityObserver
{
    /**
     * Created model event handler.
     *
     * @param Model $model
     */
    public function created(Model $model)
    {
        if (! $model->permalinkHandling()) {
            return;
        }

        $model->createPermalink(
            $this->gatherAttributes($model->getPermalinkAttributes())
        );
    }

    /**
     * Updated model event handler.
     *
     * @param Model $model
     */
    public function updated(Model $model)
    {
        if (! $model->permalinkHandling()) {
            return;
        }

        $model->updatePermalink(
            $this->gatherAttributes($model->getPermalinkAttributes())
        );
    }

    /**
     * Restored model event handler.
     *
     * @param $model
     */
    public function restored($model)
    {
        if (! $model->permalinkHandling() || ! $model->hasPermalink()) {
            return;
        }

        $model->permalink->restore();
    }

    /**
     * Deleted model event handler.
     *
     * @param Model $model
     */
    public function deleted(Model $model)
    {
        if (! $model->hasPermalink()) {
            return;
        }

        $method = 'forceDelete';

        // The Permalink record should be removed if the main entity has been
        // deleted. Here we will check if we should perform a hard or soft
        // deletion depending on what is being used ont he main entity.
        if (method_exists($model, 'trashed') && ! $model->isForceDeleting()) {
            $method = 'delete';
        }

        $model->permalink->{$method}();
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