<?php

use app\services\auth\AuthService;
use app\services\jwt\JWTTokenService;
use app\services\user\UserService;

return [
    'definitions' => [
        AuthService::class => function () {
            $jwt = new JWTTokenService();
            return new AuthService($jwt);
        },
    ],
];
