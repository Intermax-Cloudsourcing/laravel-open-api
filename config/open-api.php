<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Name
    |--------------------------------------------------------------------------
    |
    | This value is used to show the name of the API in the generated docs.
    |
    */

    'name' => env('APP_NAME', null),

    /*
    |--------------------------------------------------------------------------
    | API version
    |--------------------------------------------------------------------------
    |
    | This value is used to show the version of the API in the generated docs.
    |
    */

    'version' => env('APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Content type
    |--------------------------------------------------------------------------
    |
    | The default content type. If not overridden, it will be used for request
    | bodies as well as responses for every endpoint.
    |
    */

    'content_type' => env('OPEN_API_CONTENT_TYPE', 'application/json'),

];
