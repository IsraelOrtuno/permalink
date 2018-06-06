<?php

namespace Devio\Permalink\Middleware;

class BuildMetas
{
    public function handle($request, \Closure $next)
    {
        $this->build($request->route()->permalink());

        return $next($request);
    }

    protected function build($permalink)
    {
        if (! $permalink || ! $permalink->seo) {
            return;
        }

        foreach ($permalink->seo as $type => $meta) {
            app("permalink.$type")->translate($type, $meta);
        }
    }
}