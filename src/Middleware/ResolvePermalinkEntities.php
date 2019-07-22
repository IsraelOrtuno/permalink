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
        $route = $request->route();

        if ($route->hasPermalink()) {
            $route->parameters[] = $route->permalink()->entity;
        }

        return $next($request);
    }
}
