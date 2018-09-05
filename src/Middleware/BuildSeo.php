<?php

namespace Devio\Permalink\Middleware;

use Devio\Permalink\Contracts\Manager;

class BuildSeo
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
        app(Manager::class)->runBuilders();

        return $next($request);
    }
}