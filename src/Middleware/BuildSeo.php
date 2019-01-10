<?php

namespace Devio\Permalink\Middleware;

use Devio\Permalink\PermalinkSeo;

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
        app(PermalinkSeo::class)->request($request)->runBuilders();

        return $next($request);
    }
}