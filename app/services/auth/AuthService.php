<?php

namespace app\services\auth;

use app\models\User;
use app\models\UserRefreshToken;
use app\services\jwt\JWTTokenManager;

class AuthService
{
    public function __construct(
        public JWTTokenManager $tokenManager,
    ) {}

    public function login(User $user): string
    {
        return $this->tokenManager->generateJwt($user);
    }

    public function generateRefreshToken(User $user): UserRefreshToken
    {
        return $this->tokenManager->generateRefreshToken($user);
    }

    public function generateToken(User $user): string
    {
        return $this->tokenManager->generateJwt($user);
    }
}