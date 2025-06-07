<?php

namespace Touchepasauklaxon\Controllers;

use Touchepasauklaxon\Auth;
use Touchepasauklaxon\Models\User;
use Touchepasauklaxon\Models\Trajet;

class HomeController
{
    private function prepare()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return Auth::isLogged();
    }

    public function index()
    {
        $isLogged = $this->prepare();
        $title = 'Accueil - Touche pas au Klaxon';

        require_once dirname(__DIR__, 2) . '/app/Views/templates/header.php';
        $trajetModel = new Trajet();
        $trajets = $trajetModel->getAll();

        echo "<h2>Liste des trajets</h2>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Auteur</th><th>Date de départ</th><th>Date de destination</th><th>Ville de départ</th><th>Ville d'arrivée</th><th>Nombre de places</th>";
        if ($isLogged) {
            echo "<th>Actions</th>";
        }
        echo "</tr>";
        if($trajetModel->getLastError()) {
            echo "<tr><td colspan='8'>Erreur lors de la récupération des trajets : " . htmlspecialchars($trajetModel->getLastError()) . "</td></tr>";
        } elseif (empty($trajets)) {
            echo "<tr><td colspan='8'>Aucun trajet trouvé.</td></tr>";
        } else {
            foreach ($trajets as $trajet) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($trajet['id']) . "</td>";
                echo "<td>" . htmlspecialchars($trajet['auteur_nom']) . " " . htmlspecialchars($trajet['auteur_prenom']) . "</td>";
                echo "<td>" . htmlspecialchars($trajet['date_depart']) . "</td>";
                echo "<td>" . htmlspecialchars($trajet['date_destination']) . "</td>";
                echo "<td>" . htmlspecialchars($trajet['ville_depart']) . "</td>";
                echo "<td>" . htmlspecialchars($trajet['ville_arrivee']) . "</td>";
                echo "<td>" . htmlspecialchars($trajet['places']) . "</td>";
                if ($isLogged) {
                    echo "<td>";
                    echo "<form method='POST' action='/trajets/reserver' style='display:inline;'>";
                    echo "<input type='hidden' name='id' value='" . htmlspecialchars($trajet['id']) . "'>";
                    echo "<input type='submit' value='Details'>";
                    echo "</form>";
                }
                echo "</tr>";
            }
        }
        echo "</table>";

        require_once dirname(__DIR__, 2) . '/app/Views/templates/footer.php';
    }

    public function login()
    {
        $isLogged = $this->prepare();
        if ($isLogged) {
            header('Location: /');
            exit;
        }

        $title = 'Connexion - Touche pas au Klaxon';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/header.php';
        require_once dirname(__DIR__, 2) . '/app/Views/login.php';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/footer.php';
    }
}
