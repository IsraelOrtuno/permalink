<?php

namespace Devio\Permalink;

use Devio\Permalink\Services\NestingService;
use Cviebrock\EloquentSluggable\Services\SlugService;

class PermalinkObserver
{
    public function creating($model)
    {
        if (config('permalink.nesting.nest_to_parent_on_create') || $model->parent_id) {
            $this->nestToParent($model);
        }

        (new NestingService)->single($model);
    }

    public function saving($model)
    {
        $this->ensureSlugIsUnique($model);
    }

//    public function updated($model)
//    {
//        if (! config('permalink.nesting.regenerate_children_path_on_update')) {
//            return;
//        }
//
//        if ($model->isDirty('slug') && $model->children->count()) {
//            (new NestingService)->recursive($model);
//        }
//    }

    /**
     * Creates an unique slug for the permalink.
     *
     * @param $model
     */
    protected function ensureSlugIsUnique($model)
    {
        if (! $model->isDirty('slug') || empty($model->slug)) {
            return;
        }

        // If the user has provided an slug manually, we have to make sure
        // that that slug is unique. If it is not, the SlugService class
        // will append an incremental suffix to ensure its uniqueness.
        $model->slug = SlugService::createSlug($model, 'slug', $model->slug, []);
    }

    /**
     * Nest the permalink to a parent if found.
     *
     * @param $model
     */
    protected function nestToParent($model)
    {
        if ($model->entity && $parent = NestingService::parentFor($model->entity)) {
            $model->parent_id = $parent->getKey();
        }
    }
}