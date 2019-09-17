<?php

namespace Devio\Permalink;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Services\SlugService;

class EntityObserver
{
    /**
     * @var SlugService
     */
    private $slugService;

    /**
     * PermalinkObserver constructor.
     */
    public function __construct(SlugService $slugService)
    {
        $this->slugService = $slugService;
    }

    public function saved(Model $model)
    {
        if (! $model->permalinkHandling() && is_null($model->getPermalinkAttributes())) {
            return;
        }

        $attributes = $this->gatherAttributes($model->getPermalinkAttributes());

        // By checking if 'deleted_at' column has been modified, we can prevent
        // re-creating the permalink when the model has been restored because
        // this event is fired again and wasRecentlyCreated will be true.
        $softDeletingAction = method_exists($model, 'getDeletedAtColumn') && $model->isDirty($model->getDeletedAtColumn());

        ($model->wasRecentlyCreated && ! $softDeletingAction) ?
            $model->createPermalink($attributes) : $model->updatePermalink($attributes);
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
        if (! $model->hasPermalink() || ! $model->permalinkHandling()) {
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
