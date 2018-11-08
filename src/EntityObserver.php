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
        if (! $model->handlePermalink) {
            return;
        }

        $attributes = $this->gatherAttributes($model->getPermalinkAttributes());

        $model->updatePermalink($attributes);
    }

    /**
     * Restored model event handler.
     *
     * @param $model
     */
    public function restored($model)
    {
        $model->permalink->restore();
    }

    /**
     * Deleted model event handler.
     *
     * @param Model $model
     */
    public function deleted(Model $model)
    {
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