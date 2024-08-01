<?php

namespace app\services\jwt;

use app\models\User;
use app\models\UserRefreshToken;

interface JWTTokenManager
{
    public function generateRefreshToken(User $user,): UserRefreshToken;

    public function generateJwt(User $user): string;
}