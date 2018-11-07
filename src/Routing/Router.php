<?php

namespace Devio\Permalink\Routing;

use Illuminate\Routing\Router as Laravelrouter;

class Router extends LaravelRouter
{
    /**
     * Load the given permalinks or fetch them from database.
     *
     * @param null $permalinks
     */
    public function loadPermalinks($permalinks = null)
    {
        if (is_null($permalinks)) {
            $this->clearPermalinkRoutes();
        }

        $permalinks = (new RouteCollection(
            array_filter(array_wrap($permalinks))
        ))->tree();

        $this->group(config('permalink.group'), function () use ($permalinks) {
            $this->addPermalinks($permalinks);
        });

        // Whenever routes are loaded, we should refresh the name lookups to
        // make sure all our newly generated route names are included into
        // the route collection name list. Routes can be added any time.
        $this->refreshRoutes();
    }

    protected function refreshRoutes()
    {
        $this->getRoutes()->refreshNameLookups();
        $this->getRoutes()->refreshActionLookups();
    }

    public function clearPermalinkRoutes()
    {
        $routeCollection = new \Illuminate\Routing\RouteCollection;

        collect($this->getRoutes())->filter(function ($route) {
            return ! $route instanceof Route;
        })->each(function ($route) use ($routeCollection) {
            $routeCollection->add($route);
        });

        $this->setRoutes($routeCollection);
    }

    /**
     * Create a new permalink route.
     *
     * @param $permalink
     * @return Route
     */
    protected function createRouteForPermalink($permalink)
    {
        $route = $this->newPermalinkRoute($permalink);

        // If we have groups that need to be merged, we will merge them now after this
        // route has already been created and is ready to go. After we're done with
        // the merge we will be ready to return the route back out to the caller.
        if ($this->hasGroupStack()) {
            $this->mergeGroupAttributesIntoRoute($route);
        }

        $this->addWhereClausesToRoute($route);

        return $route;
    }

    /**
     * Create a new Route for the given permalink.
     *
     * @param $permalink
     * @return Route
     */
    protected function newPermalinkRoute($permalink)
    {
        $path = $this->prefix($permalink->slug);
        $action = $this->convertToControllerAction($permalink->action);

        return (new Route($permalink->method, $path, $action, $permalink))
            ->setRouter($this)->setContainer($this->container);
    }

    /**
     * Add a collection of permalinks to the router.
     *
     * @param array $permalinks
     */
    public function addPermalinks($permalinks = [])
    {
        foreach ($permalinks as $permalink) {
            $this->addPermalink($permalink);
        }
    }

    /**
     * Add a single permalink to the router.
     *
     * @param $permalink
     */
    public function addPermalink($permalink)
    {
        if ($permalink->action) {
            $route = $this->createRouteForPermalink($permalink);

            $this->routes->add($route);
        }

        if (count($permalink->children)) {
            $this->permalinkGroup($permalink);
        }
    }

    /**
     * Create a new permalink route group.
     *
     * @param $permalink
     */
    public function permalinkGroup($permalink)
    {
        $this->group(['prefix' => $permalink->slug], function () use ($permalink) {
            $this->addPermalinks($permalink->children);
        });
    }
}