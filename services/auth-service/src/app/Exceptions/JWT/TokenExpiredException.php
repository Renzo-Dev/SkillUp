<?php

namespace App\Exceptions\JWT;

class TokenExpiredException extends JwtException
{
    protected $message = 'JWT токен истек';
}

