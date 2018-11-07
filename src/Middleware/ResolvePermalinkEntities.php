<?php

namespace Devio\Permalink\Middleware;

use Devio\Permalink\Routing\Route;

class ResolvePermalinkEntities
{
    /**
     * Handle the incoming request.
     *
     * @param $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        if (($route = $request->route()) instanceof Route) {
            $route->parameters[] = $route->getPermalink()->entity;
        }

        return $next($request);
    }
}
