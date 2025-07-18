<?php

/**
 * Allowed route items without sign-in
 */

return [
    // Allow all Api requests
    'Prefixes' => ['Api'],
    // Allow Social requests from crawlers, so they work correctly
    'Controllers' => ['Social', 'DebugKit'],
    // Allow the following controller actions
    'ControllerActions' => [
        'Users' => ['login', 'logout', 'token'],
    ],
];
