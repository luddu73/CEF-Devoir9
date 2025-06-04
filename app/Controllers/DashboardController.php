<?php
namespace Touchepasauklaxon\Controllers;

use Touchepasauklaxon\Auth;

class DashboardController
{
    public function index()
    {
        session_start();
        if (!Auth::isLogged()) {
            header("Location: /");
            exit;
        }

        $user = Auth::getUser();
        echo "Bienvenue " . htmlspecialchars($user['nom']) . " " . htmlspecialchars($user['prenom']) . " sur votre tableau de bord !";
        echo "<a href='/logout'>Se déconnecter</a>";
    }
}