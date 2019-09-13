<?php

namespace Devio\Permalink;

use Devio\Permalink\Services\NestingService;
use Cviebrock\EloquentSluggable\Services\SlugService;

class PermalinkObserver
{
    /**
     * @var SlugService
     */
    private $slugService;

    /**
     * PermalinkObserver constructor.
     *
     * @param SlugService $slugService
     */
    public function __construct(SlugService $slugService)
    {
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

        (new NestingService)->single($permalink);
    }
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
            (new NestingService)->single($permalink);
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
        if (! $permalink->exists && $permalink->entity && $parent = NestingService::parentFor($permalink->entity)) {
            $permalink->parent_id = $parent->getKey();
            $permalink->setRelation('parent', $parent);
        }
    }
}
