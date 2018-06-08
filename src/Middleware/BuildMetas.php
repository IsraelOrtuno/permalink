<?php

namespace Devio\Permalink\Middleware;

class BuildMetas
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
        $this->build($request->route()->permalink());

        return $next($request);
    }

    /**
     * Run the builders for the current permalink.
     *
     * @param $permalink
     */
    protected function build($permalink)
    {
        if (! $permalink || ! $permalink->seo) {
            return;
        }

        foreach ($permalink->seo as $type => $meta) {
            app("permalink.$type")->build($type, $meta);
        }
    }
}