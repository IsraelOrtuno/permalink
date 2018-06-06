<?php

namespace Devio\Permalink\Routing;

use Illuminate\Routing\Router;
use Devio\Permalink\Contracts\ActionResolver;

class Route
{
    /**
     * The router instance.
     *
     * @var Router
     */
    protected $router;

    /**
     * @var ActionResolver
     */
    protected $resolver;

    /**
     * Node constructor.
     *
     * @param Router $router
     * @param ActionResolver $resolver
     */
    public function __construct(Router $router, ActionResolver $resolver)
    {
        $this->router = $router;
        $this->resolver = $resolver;
    }

    /**
     * Register a permalink into the router.
     *
     * @param $permalink
     */
    public function register($permalink)
    {
        count($permalink->children) ?
            $this->group($permalink) : $this->route($permalink);
    }

    /**
     * Add a simple route to the router.
     *
     * @param $permalink
     * @return \Illuminate\Routing\Route
     */
    protected function route($permalink)
    {
        $action = $this->resolver->resolve($permalink);

        $route = $this->router->get($permalink->slug, $action);

        if ($permalink->permalinkable) {
            $route->defaults($permalink->permalinkable_type, $permalink->permalinkable);
        }
    }

    /**
     * Create a route group into the router and register its children.
     *
     * @param $parent
     */
    protected function group($parent)
    {
        if ($parent->action || $parent->pemalinkable) {
            $this->route($parent);
        }

        // If the parent has an action or a permalinkable model we will create
        // that route as "root" just before creating any children into the
        // route group, then the children can be nested within a group.
        $callback = function () use ($parent) {
            foreach ($parent->children as $permalink) {
                $this->register($permalink);
            }
        };

        $this->router->group(['prefix' => $parent->slug], $callback);
    }
}