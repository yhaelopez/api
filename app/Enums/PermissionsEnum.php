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

    case ARTISTS_VIEW_ANY = 'artists.viewAny';
    case ARTISTS_VIEW = 'artists.view';
    case ARTISTS_CREATE = 'artists.create';
    case ARTISTS_UPDATE = 'artists.update';
    case ARTISTS_DELETE = 'artists.delete';
    case ARTISTS_RESTORE = 'artists.restore';
    case ARTISTS_FORCE_DELETE = 'artists.forceDelete';

    case ADMINS_VIEW_ANY = 'admins.viewAny';
    case ADMINS_VIEW = 'admins.view';
    case ADMINS_CREATE = 'admins.create';
    case ADMINS_UPDATE = 'admins.update';
    case ADMINS_DELETE = 'admins.delete';
    case ADMINS_RESTORE = 'admins.restore';
    case ADMINS_FORCE_DELETE = 'admins.forceDelete';

    public static function getUserPermissions(): array
    {
        return [
            self::USERS_VIEW_ANY->value,
            self::USERS_VIEW->value,
            self::USERS_CREATE->value,
            self::USERS_UPDATE->value,
            self::USERS_DELETE->value,
            self::USERS_RESTORE->value,
            // Removed USERS_FORCE_DELETE for users
        ];
    }

    public static function getArtistPermissions(): array
    {
        return [
            self::ARTISTS_VIEW_ANY->value,
            self::ARTISTS_VIEW->value,
            self::ARTISTS_CREATE->value,
            self::ARTISTS_UPDATE->value,
            self::ARTISTS_DELETE->value,
            self::ARTISTS_RESTORE->value,
            // Removed ARTISTS_FORCE_DELETE for users
        ];
    }

    public static function getAdminPermissions(): array
    {
        return [
            self::USERS_VIEW_ANY->value,
            self::USERS_VIEW->value,
            self::USERS_CREATE->value,
            self::USERS_UPDATE->value,
            self::USERS_DELETE->value,
            self::USERS_RESTORE->value,
            self::USERS_FORCE_DELETE->value,
            self::ARTISTS_VIEW_ANY->value,
            self::ARTISTS_VIEW->value,
            self::ARTISTS_CREATE->value,
            self::ARTISTS_UPDATE->value,
            self::ARTISTS_DELETE->value,
            self::ARTISTS_RESTORE->value,
            self::ARTISTS_FORCE_DELETE->value,
            self::ADMINS_VIEW_ANY->value,
            self::ADMINS_VIEW->value,
            self::ADMINS_CREATE->value,
            self::ADMINS_UPDATE->value,
            self::ADMINS_DELETE->value,
            self::ADMINS_RESTORE->value,
            self::ADMINS_FORCE_DELETE->value,
        ];
    }

    public static function getAdminManagementPermissions(): array
    {
        return [
            self::ADMINS_VIEW_ANY->value,
            self::ADMINS_VIEW->value,
            self::ADMINS_CREATE->value,
            self::ADMINS_UPDATE->value,
            self::ADMINS_DELETE->value,
            self::ADMINS_RESTORE->value,
            self::ADMINS_FORCE_DELETE->value,
        ];
    }

    public static function getAllPermissions(): array
    {
        return array_merge(
            self::getUserPermissions(),
            self::getArtistPermissions(),
        );
    }
}
