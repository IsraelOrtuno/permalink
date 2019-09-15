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
        'prefix'     => '',
        'middleware' => [
            'web',
            \Devio\Permalink\Middleware\ResolvePermalinkEntities::class
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Nesting Options
    |--------------------------------------------------------------------------
    |
    | These options control the nesting automation. By default, if a permalink
    | has a parent_for value for a certain model, it'll be automatically set
    | as child of that record. Disable to manually control this behaviour.
    |
    | Also you can decide if the package should take care of the nested slug
    | consistency. If you update a parent slug, the package will make sure
    | all its nested (recursive) permalinks gets their paths updated to
    | match that slug. If you want to control ths behaviour, disable.
    | Check the NestingService class to understand how it works.
    */

    'nest_to_parent_on_create'              => true,
    'rebuild_children_final_path_on_update' => true,


    /*
    |--------------------------------------------------------------------------
    | Route Name
    |--------------------------------------------------------------------------
    |
    | Whenever a permalink is registered as a Laravel Route, it will receive a
    | name. Here you can customize the name that will be suffixed by the key
    | of the permalink (name.id). You could also set a fallback method in
    | your models to make this names more
    |
    */

    'route_name' => 'permalink',

    /*
    |--------------------------------------------------------------------------
    | Automatically Refresh Routes
    |--------------------------------------------------------------------------
    |
    | The route's collection has to be refreshed when a new permalink is added
    | to the router. If you are adding multiple permalinks in a row, you may
    | consider to disable this feature to prevent performance issues.
    |
    | Use Devio\Permalink\Routing\Router::refreshRoutes() to refresh the route look-ups.
    |
    */

    'refresh_route_lookups' => true
];
