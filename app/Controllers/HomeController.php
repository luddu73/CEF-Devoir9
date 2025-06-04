<?php

namespace Touchepasauklaxon\Controllers;

class HomeController
{
    public function index()
    {
        $title = 'Accueil - Touche pas au Klaxon';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/header.php';
        require_once dirname(__DIR__, 2) . '/app/Views/index.php';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/footer.php';
    }
}
