<?php

namespace Devio\Permalink\Routing;

use Illuminate\Routing\Router as LaravelRouter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Router extends LaravelRouter
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Routing\Route|void
     */
    public function findRoute($request)
    {
        // First we'll try to find any code defined route for the current request.
        // If no route was found, we can then attempt to find if the URL path
        // matches a existing permalink. If not just rise up the exception.
        try {
            return parent::findRoute($request);
        } catch (NotFoundHttpException $e) {
            $this->findPermalink($request);
            $this->refreshRoutes();

            return parent::findRoute($request);
        }
    }

    public function findPermalink($request)
    {
        $permalink = (new Query($request))->match();

        if ($permalink) {
            return $this->addPermalinks([$permalink]);
        }

        throw new NotFoundHttpException;
    }

    protected function createPermalinkRoute($permalink)
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
            $route = $this->createPermalinkRoute($permalink);

            $this->routes->add($route);
        }

        if ($permalink->relationLoaded('children')) {
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

    /**
     * Refesh the route name and action lookups.
     */
    protected function refreshRoutes()
    {
        $this->getRoutes()->refreshNameLookups();
        $this->getRoutes()->refreshActionLookups();
    }
}