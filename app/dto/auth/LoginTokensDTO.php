<?php

namespace app\dto\auth;

class LoginTokensDTO
{
    public function __construct(public string $token, public string $refreshToken) {}
}
