<?php

namespace Devio\Permalink;

use Devio\Permalink\Services\PathBuilder;
use Cviebrock\EloquentSluggable\Services\SlugService;

class PermalinkObserver
{
    /**
     * The PathBuilder instance.
     *
     * @var PathBuilder
     */
    protected $path;

    /**
     * The SlugService instance.
     *
     * @var SlugService
     */
    protected $slugService;

    /**
     * PermalinkObserver constructor.
     *
     * @param PathBuilder $path
     * @param SlugService $slugService
     */
    public function __construct(PathBuilder $path, SlugService $slugService)
    {
        $this->path = $path;
        $this->slugService = $slugService;
    }

    /**
     * Creating event.
     *
     * @param $model
     */
    public function creating($permalink)
    {
        $this->nestToParent($permalink);

        if ($permalink->isDirty('slug') && ! empty($permalink->slug)) {
            $this->ensureSlugIsUnique($permalink);
        } else {
            $this->slugService->slug($permalink);
        }

        $this->path->build($permalink);
    }

    /**
     * Updating event.
     *
     * @param $model
     */
    public function updating($permalink)
    {
        if ($permalink->getOriginal('slug') !== $permalink->slug) {
            $this->ensureSlugIsUnique($permalink);

            config('rebuild_children_final_path_on_update')
                ? $this->path->recursive($permalink)
                : $this->path->single($permalink);
        }
    }

    /**
     * Creates an unique slug for the permalink.
     *
     * @param $model
     */
    protected function ensureSlugIsUnique($permalink)
    {
        if (! $permalink->isDirty('slug') || empty($permalink->slug)) {
            return;
        }

        // If the user has provided an slug manually, we have to make sure
        // that that slug is unique. If it is not, the SlugService class
        // will append an incremental suffix to ensure its uniqueness.
        $permalink->slug = SlugService::createSlug($permalink, 'slug', $permalink->slug, []);
    }

    /**
     * Nest the permalink to a parent if found.
     *
     * @param $model
     */
    protected function nestToParent($permalink)
    {
        if (! config('permalink.nest_to_parent_on_create')) {
            return;
        }

        $parent = PathBuilder::parentFor($permalink->entity);

        if (! $permalink->exists && $permalink->entity && $parent) {
            $permalink->parent_id = $parent->getKey();
            $permalink->setRelation('parent', $parent);
        }
    }
}
