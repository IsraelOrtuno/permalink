<?php

namespace Devio\Permalink;

use Devio\Permalink\Contracts\Permalinkable;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class PermalinkableObserver
{
//    /**
//     * Created model event handler.
//     *
//     * @param Model $model
//     */
//    public function created(Model $model)
//    {
//        if (! $this->managePermalinks($model)) {
//            return;
//        }
//
//        $model->permalink()->create($this->gatherAttributes($model->getPermalinkAttributes()));
//    }

    public function saved(Model $model)
    {
        if (! $this->managePermalinks($model)) {
            return;
        }

        if ($model->wasRecentlyCreated) {
            $model->permalink()->create($this->gatherAttributes($model->getPermalinkAttributes()));
        } elseif ($model->permalink) {
            $model->permalink->update($this->gatherAttributes($model->getPermalinkAttributes()));
        }
    }

//    /**
//     * Updated model event handler.
//     *
//     * @param Model $model
//     */
//    public function updated(Model $model)
//    {
//        dd($model->saved);
//        if (! $this->managePermalinks($model) || ! $model->permalink) {
//            return;
//        }
//
//        dd('test');
//
//        $model->permalink()->update($this->gatherAttributes($model->getPermalinkAttributes()));
//    }

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