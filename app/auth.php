<?php

namespace Touchepasauklaxon;

class Auth 
{
    public static function isLogged()
    {
        return isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['id']);
    }

    public static function isAdmin()
    {
        return self::isLogged() && !empty($_SESSION['user']['isAdmin']);
    }

    public static function getUser()
    {
        return self::isLogged() ? $_SESSION['user'] : null;
    }
}