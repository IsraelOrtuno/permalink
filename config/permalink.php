<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Middleware
    |--------------------------------------------------------------------------
    |
    | This option is passed to the parent Route group where all routes will be
    | registered in. Modify this array to include any extra option on top of
    | every route. Keep the same signature as if registering a route group.
    |
    */

    'group' => [
        'middleware' => [
            'web'
        ]
    ],

    'automatic_nesting' => true,

    /*
    |--------------------------------------------------------------------------
    | Automatically Refresh Routes
    |--------------------------------------------------------------------------
    |
    | The route's collection has to be refreshed when a new permalink is added
    | to the router. Consider setting this option to false and refresh them
    | manually if using addPermalinks many times for a better performance.
    |
    | Use Router::refreshRoutes() method to refresh the route look-ups.
    |
    */

    'refresh_route_lookups' => true
];