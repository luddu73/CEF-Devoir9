<?php

namespace Touchepasauklaxon\Controllers;

use Touchepasauklaxon\Models\User;

class HomeController
{
    public function index()
    {
        $title = 'Accueil - Touche pas au Klaxon';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/header.php';
        require_once dirname(__DIR__, 2) . '/app/Views/index.php';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/footer.php';
    }

    public function testBdd()
    {
        $userModel = new User();
        $users = $userModel->getAll();

        // On inclut la vue en lui passant les données
        require __DIR__ . '/../Views/user.php';
    }
}
