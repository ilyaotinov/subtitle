<?php

return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => true,
    'rules' => [
        'POST auth/login' => 'auth/login',
        'GET /' => 'site/index',
        'GET auth/refresh-token' => 'auth/refresh-token',
        'DELETE auth/refresh-token' => 'auth/delete-refresh-token',
        'POST users/register' => 'user/register'
    ],
];