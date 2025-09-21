<?php
/**
 * Classe de gestion de l'authentification des utilisateurs.
 *
 * Centralise les vérifications d'authentification et les informations
 * sur l'utilisateur connecté.
 *
 * @category Auth
 * @package  TouchePasAuKlaxon
 */

namespace Touchepasauklaxon;

class Auth 
{
    /**
     * Vérifie si un utilisateur est connecté.
     *
     * @return bool Vrai si un utilisateur est connecté, faux sinon.
     */
    public static function isLogged(): bool
    {
        return isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['id']);
    }

    /**
     * Vérifie si l'utilisateur connecté est un administrateur.
     * 
     * @return bool Vrai si l'utilisateur est admin, faux sinon.
     */
    public static function isAdmin(): bool
    {
        return self::isLogged()
            && is_array($_SESSION['user'])
            && !empty($_SESSION['user']['isAdmin'])
            && $_SESSION['user']['isAdmin'] == 1;
    }

    /**
     * Si l'utilisateur n'est pas un admin alors qu'il devrait l'être, on le redirige
     * 
     * @return void Vrai si l'utilisateur est admin, faux sinon.
     */
    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            header("Location: /");
            exit;
        }
    }

    /**
     * Retourne les informations de l'utilisateur connecté.
     * 
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