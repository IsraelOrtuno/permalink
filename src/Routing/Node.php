<?php

namespace Devio\Permalink\Routing;

use Illuminate\Routing\Router;

class Node
{
    /**
     * The router instance.
     *
     * @var Router
     */
    protected $router;

    /**
     * Node constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Alias for creating and registering a permalink.
     *
     * @param $router
     * @param $permalink
     */
    public static function make($router, $permalink)
    {
        return (new static($router))->register($permalink);
    }

    /**
     * Register a permalink into the router.
     *
     * @param $permalink
     */
    public function register($permalink)
    {
        if (count($permalink->children)) {
            return $this->group($permalink);
        }

        $this->route($permalink);
    }

    /**
     * Add a simple route to the router.
     *
     * @param $permalink
     * @return \Illuminate\Routing\Route
     */
    protected function route($permalink)
    {
        $action = 'App\\Http\\Controllers\\UserController@index';

        $route = $this->router->get($permalink->slug, $action);

        if ($permalink->permalinkable) {
            $route->defaults($permalink->permalinkable_type, $permalink->permalinkable);
        }

        return $route;
    }

    /**
     * Create a route group into the router and register its children.
     *
     * @param $parent
     */
    protected function group($parent)
    {
        $callback = function () use ($parent) {
            if ($parent->permalinkable) {
                $this->route($parent);
            }

            foreach ($parent->children as $permalink) {
                $this->register($permalink);
            }
        };

        $this->router->group(['prefix' => $parent->slug], $callback);
    }
}