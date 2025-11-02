<?php

namespace App\Exceptions\JWT;

class TokenBlacklistedException extends JwtException
{
    protected $message = 'JWT токен отозван';
}

