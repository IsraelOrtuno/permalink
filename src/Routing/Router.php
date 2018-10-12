<?php

namespace Devio\Permalink\Routing;

use Devio\Permalink\Permalink;
use Illuminate\Routing\Router as LaravelRouter;
use Illuminate\Contracts\Foundation\Application;
use Devio\Permalink\Contracts\Router as PermalinkRouter;

class Router implements PermalinkRouter
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * The router instance.
     *
     * @var Router
     */
    protected $router;

    /**
     * Router constructor.
     *
     * @param LaravelRouter $router
     */
    public function __construct(LaravelRouter $router)
    {
        $this->router = $router;
    }

    /**
     * Load the given set of routes.
     *
     * @param null $permalinks
     */
    public function load($permalinks = null)
    {
        if (! \Schema::hasTable('permalinks')) {
            return;
        }

        $permalinks = is_null($permalinks) ? $this->getPermalinkTree() : array_wrap($permalinks);

        $callback = function ($router) use ($permalinks) {
            foreach ($permalinks as $permalink) {
                (new Route($this->router))->register($permalink);
            }
        };

        $this->router->group(config('permalink.group'), $callback);

        // Whenever routes are loaded, we should refresh the name lookups to
        // make sure all our newly generated route names are included into
        // the route collection name list. Routes can be added any time.
        app('router')->getRoutes()->refreshNameLookups();
    }

    /**
     * Get all the permalinks in a tree structure.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getPermalinkTree()
    {
        // We will query all the root permalinks and then load all their children
        // relationships recursively. This way we will obtain a tree structured
        // collection in which we can easily iterate from parents to children.
        return Permalink::with('children', 'permalinkable')
                        ->whereNull('parent_id')
                        ->get();
    }
}