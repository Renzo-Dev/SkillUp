<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case TRIAL = 'trial';
    case ACTIVE = 'active';
    case GRACE = 'grace';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
}

