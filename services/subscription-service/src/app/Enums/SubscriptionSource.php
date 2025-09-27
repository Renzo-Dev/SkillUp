<?php

namespace App\Enums;

enum SubscriptionSource: string
{
    case PAYMENT_SERVICE = 'payment_service';
    case MANUAL = 'manual';
    case ADMIN = 'admin';
}

