<?php

namespace Touchepasauklaxon;

class Auth 
{
    public static function isLogged(): bool
    {
        return isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['id']);
    }

    public static function isAdmin(): bool
    {
        return self::isLogged() && !empty($_SESSION['user']['isAdmin']) && $_SESSION['user']['isAdmin'] == 1;
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            header("Location: /dashboard");
            exit;
        }
    }

    public static function getUser()
    {
        return self::isLogged() ? $_SESSION['user'] : null;
    }
}