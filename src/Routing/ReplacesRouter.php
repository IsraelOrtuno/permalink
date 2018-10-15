<?php

namespace Devio\Permalink\Routing;

trait ReplacesRouter
{
    /**
     * Replace the default router before replacing it.
     *
     * @return mixed
     */
    public function dispatchToRouter()
    {
        $this->router = $this->app['router'];

        $this->router->replaceMiddleware($this->routeMiddleware, $this->middlewareGroups);

        return parent::dispatchToRouter();
    }
}