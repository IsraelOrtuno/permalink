<?php

use Devio\Permalink\Services\RouteService;

if (! function_exists('permalink')) {
    /**
     * Helper for accessing a permalink route.
     */
    function permalink($permalink)
    {
        return app(\Devio\Permalink\PermalinkManager::class)
                    ->route($permalink);
    }
}