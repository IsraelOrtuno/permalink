<?php

namespace Devio\Permalink\Middleware;

use Devio\Permalink\Permalink;

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
            foreach ($route->signatureParameters() as $parameter) {
                $type = $parameter->getType();

                if ($type && $type->getName() == Permalink::class) {
                    $route->parameters[] = $route->permalink();
                } else {
                    $route->parameters[] = $route->permalink()->entity;
                }
            }
        }

        return $next($request);
    }
}
