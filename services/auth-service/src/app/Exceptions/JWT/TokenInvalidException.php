<?php

namespace App\Exceptions\JWT;

class TokenInvalidException extends JwtException
{
    protected $message = 'JWT токен недействителен';
}

