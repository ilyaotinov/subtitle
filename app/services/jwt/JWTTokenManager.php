<?php

namespace app\services\jwt;

use app\models\User;
use app\models\UserRefreshToken;

interface JWTTokenManager
{
    public function generateRefreshToken(): string;

    public function generateJwt(User $user): string;
}