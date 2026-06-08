<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | Applied to all erpaccount web and API routes. Override in the host app
    | after publishing config, e.g. ['web', 'auth'] or ['api', 'auth:sanctum'].
    |
    */
    'route_middleware' => ['web', 'auth'],

    'api_route_middleware' => ['api', 'auth:sanctum'],
];
