<?php

namespace App\Enums;

enum GuardEnum: string
{
    case WEB = 'web';
    case ADMIN = 'admin';
    case API = 'api';
    case SANCTUM = 'sanctum';
}
