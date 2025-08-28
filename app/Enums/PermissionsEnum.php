<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    case USERS_VIEW_ANY = 'users.viewAny';
    case USERS_VIEW = 'users.view';
    case USERS_CREATE = 'users.create';
    case USERS_UPDATE = 'users.update';
    case USERS_DELETE = 'users.delete';
    case USERS_RESTORE = 'users.restore';
    case USERS_FORCE_DELETE = 'users.forceDelete';

    public static function getUserPermissions(): array
    {
        return [
            self::USERS_VIEW_ANY->value,
            self::USERS_VIEW->value,
            self::USERS_CREATE->value,
            self::USERS_UPDATE->value,
            self::USERS_DELETE->value,
            self::USERS_RESTORE->value,
            self::USERS_FORCE_DELETE->value,
        ];
    }

    public static function getAllPermissions(): array
    {
        return array_merge(
            self::getUserPermissions(),
            // Future permission types will be added here:
        );
    }
}
