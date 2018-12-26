<?php

namespace Devio\Permalink\Routing;

use Illuminate\Http\Request;
use Devio\Permalink\Permalink;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Matcher
{
    /**
     * The current request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * Query constructor.
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    public function match()
    {
        return $this->matchAgainstSegments();
    }

    // USE CASES
    // - Route may be /  <---- CONSIDER THAT
    // - First segments (before reversing) may be part of a global group <---- 💁‍♀️ DEAL WITH IT
    // - 
    protected function matchAgainstSegments()
    {
        $segments = $this->segments();

        try {
            $current = $permalink = $this->getRoot($segments[0] ?? '');

            while ($current && $segment = next($segments)) {
                $nested = $this->getNested($current, $segment);

                $current->setRelation('children', new Collection([$nested]));

                $current = $nested;
            }
        } catch (ModelNotFoundException $e) {
            return false;
        }

        return $permalink;
    }

    /**
     * Get the root permalink for a given slug.
     *
     * @param $slug
     * @return mixed
     */
    protected function getRoot($slug)
    {
        return $this->query()->where('slug', $slug)
                    ->where('parent_id', null)
                    ->firstOrFail();
    }

    /**
     * Get the nested permalink of a given permalink based on a slug.
     *
     * @param $permalink
     * @param $slug
     * @return \Illuminate\Support\Collection
     */
    protected function getNested($permalink, $slug)
    {
        return $permalink->children(false)
                         ->where('slug', $slug)
                         ->firstOrFail();
    }

    /**
     * Get the request segments.
     *
     * @return mixed
     */
    protected function segments()
    {
        return $this->request->segments();
    }

    /**
     * Get a new query
     * y.
     *
     * @return Permalink
     */
    protected function query()
    {
        return (new Permalink);
    }
}