<?php

namespace Devio\Permalink\Routing;

use Illuminate\Routing\Router;
use Illuminate\Database\Eloquent\Relations\Relation;

class Route
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
     * @return void
     */
    protected function route($permalink)
    {
        $route = $this->router->get($permalink->slug, $permalink->action)
                              ->name($this->getRouteName($permalink));

        if ($permalink->permalinkable) {
            $route->defaults(
                Relation::getMorphedModel($permalink->permalinkable_type) ?? $permalink->permalinkable_type, $permalink->permalinkable
            );
        }

        // We will bind our permalink model to the model itself. This way access
        // the permalink directly from the current Route instance. It'll even
        // keep bound when the application's route list has been cached.
        $route->permalink($permalink);
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

    /**
     * Get the route name.
     *
     * @param $permalink
     * @return null|string
     */
    protected function getRouteName($permalink)
    {
        if ($permalinkable = $permalink->permalinkable) {
            return $permalinkable->permalinkRouteName() . '.' . $permalink->getKey();
        }

        $action = $permalink->rawAction;

        return str_contains($action, '@') ? $this->getRouteNameFromAction($action) : $action;
    }

    /**
     * Extract the route name from the fully qualified action.
     *
     * @param $action
     * @return string
     * @throws \ReflectionException
     */
    protected function getRouteNameFromAction($action)
    {
        list ($class, $method) = explode('@', $action);
        $name = (new \ReflectionClass($class))->getShortName();

        $name = str_replace('Controller', '', $name);

        return strtolower($name . '.' . $method);
    }
}