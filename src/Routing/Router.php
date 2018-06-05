<?php

namespace Devio\Permalink\Routing;

use Devio\Permalink\Permalink;
use Devio\Permalink\Contracts\ActionResolver;
use Illuminate\Routing\Router as LaravelRouter;
use Illuminate\Contracts\Foundation\Application;

class Router
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
     * The resolver instance.
     *
     * @var ActionResolver
     */
    protected $resolver;

    /**
     * Router constructor.
     *
     * @param LaravelRouter $router
     * @param ActionResolver $resolver
     */
    public function __construct(LaravelRouter $router, ActionResolver $resolver)
    {
        $this->router = $router;
        $this->resolver = $resolver;
    }

    /**
     * Load the given set of routes.
     *
     * @param $pages
     */
    public function load()
    {
        $callback = function ($router) {
            foreach ($this->getPermalinkTree() as $permalink) {
                (new Route($this->router, $this->resolver))->register($permalink);
            }
        };

        $this->router->group(['middleware' => 'web'], $callback);
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