<?php

use Devio\Permalink\Services\RouteService;

if (! function_exists('permalink')) {
    /**
     * Helper for accessing a permalink route.
     */
    function permalink($permalink)
    {
        return (new RouteService())
            ->permalink($permalink);
    }
}