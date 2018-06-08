<?php

namespace Devio\Permalink;

use Devio\Permalink\Contracts\Permalinkable;
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

        // Once we have the attributes we need to set, we will perform a new
        // query in order to find if there is any parent class set for the
        // current permalinkable entity. If so, we'll add it as parent.
        if ($parent = Permalink::where('parent_for', $model->getMorphClass())->first()) {
            $attributes['parent_id'] = $parent->getKey();
        }

        // Then we are ready to perform the creation or update action based on
        // the model existence. If the model was recently created, we'll add
        // a new permalink, otherwise, we'll update the existing permalink.
        if ($model->wasRecentlyCreated) {
            $model->permalink()->create($attributes);
        } elseif ($model->permalink) {
            $model->permalink->update($attributes);
        }
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