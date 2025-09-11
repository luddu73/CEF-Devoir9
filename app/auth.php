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
        return self::isLogged()
            && is_array($_SESSION['user'])
            && !empty($_SESSION['user']['isAdmin'])
            && $_SESSION['user']['isAdmin'] == 1;
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            header("Location: /");
            exit;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getUser(): ?array
    {
        return self::isLogged() && is_array($_SESSION['user'])
            ? array_combine(
                array_map('strval', array_keys($_SESSION['user'])),
                array_values($_SESSION['user'])
            )
            : null;
    }
}