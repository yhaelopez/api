<?php

namespace App\Enums;

enum GuardEnum: string
{
    case WEB = 'web';
    case API = 'api';
    case SANCTUM = 'sanctum';
}
