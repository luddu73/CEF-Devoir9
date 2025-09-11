<?php

namespace Touchepasauklaxon\Controllers;

use Touchepasauklaxon\Auth;
use Touchepasauklaxon\Models\User;
use Touchepasauklaxon\Models\Trajet;
use Touchepasauklaxon\Models\Agence;

class HomeController
{
    private function prepare(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return Auth::isLogged();
    }

    public function index(): void
    {
        $isLogged = $this->prepare();
        $isAdmin = Auth::isAdmin();
        $title = 'Accueil - Touche pas au Klaxon';

        require_once dirname(__DIR__, 2) . '/app/Views/templates/header.php';
        $trajetModel = new Trajet();
        $trajets = $trajetModel->getAll();
        if (isset($_SESSION['flashMsg'])) {
            echo "<p><center>" . htmlspecialchars($_SESSION['flashMsg']) . "</center></p>";
            unset($_SESSION['flashMsg']);
        }
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
                echo "<td>" . htmlspecialchars($trajet['agence_depart']) . "</td>";
                echo "<td>" . htmlspecialchars($trajet['agence_destination']) . "</td>";
                echo "<td>" . htmlspecialchars($trajet['places']) . "</td>";
                if ($isLogged) {
                    echo "<td>";
                    echo "<a href='/detail/" . htmlspecialchars($trajet['id']) . "' style='display:inline;'>";
                    echo "<input type='button' value='Details'>";
                    echo "</a>";
                  if($trajet['auteur'] === $_SESSION['user']['id']) {
                        echo "<a href='/modifier/" . htmlspecialchars($trajet['id']) . "' style='display:inline;'>";
                        echo "<input type='button' value='Modifier'>";
                        echo "</a>";
                        echo "<form method='POST' action='/delete' style='display:inline;'>";
                        echo "<input type='hidden' name='id' value='" . htmlspecialchars($trajet['id']) . "'>";
                        echo "<input type='submit' value='Supprimer'>";
                        echo "</form>";
                    }
                }
                echo "</tr>";
            }
        }
        echo "</table>";

        require_once dirname(__DIR__, 2) . '/app/Views/templates/footer.php';
    }

    public function login(): void
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

    public function createTrajetForm(): void
    {
        $isLogged = $this->prepare();
        $isAdmin = Auth::isAdmin();
       if(!$isLogged) {
            header('Location: /login');
            exit;
        }
        $title = 'Créer un trajet - Touche pas au Klaxon';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/header.php';
        $agences = (new Agence())->getAll();
        require_once dirname(__DIR__, 2) . '/app/Views/creer-trajet.php';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/footer.php';
    }

    public function editTrajetForm(int $id): void
    {
        $isLogged = $this->prepare();
        $isAdmin = Auth::isAdmin();
       if(!$isLogged) {
            header('Location: /login');
            exit;
        }

        $trajetModel = new Trajet();
        $trajet = $trajetModel->getById($id);
        if (!$trajet) {
            $_SESSION['flashMsg'] = "Trajet non trouvé.";
            header('Location: /');
            exit;
        }

        if ($trajet['auteur'] !== $_SESSION['user']['id']) {
            $_SESSION['flashMsg'] = "Vous n'êtes pas autorisé à modifier ce trajet.";
            header('Location: /');
            exit;
        }

        $title = 'Modifier un trajet - Touche pas au Klaxon';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/header.php';
        $agences = (new Agence())->getAll();
        require_once dirname(__DIR__, 2) . '/app/Views/modif-trajet.php';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/footer.php';
    }

    /**
     * @return array<int, string>
     */
    private function controleTrajet(): array
    {
        $dateDepart = $_POST['date_depart'] ?? '';
        $dateDestination = $_POST['date_destination'] ?? '';
        $villeDepart = $_POST['agence_depart'] ?? '';
        $villeArrivee = $_POST['agence_destination'] ?? '';
        $heureDepart = $_POST['heure_depart'] ?? '';
        $heureDestination = $_POST['heure_destination'] ?? '';
        $places = $_POST['places'] ?? '';

        $now = new \DateTime(); // Date et heure actuelles
        $dateDepartTime = \DateTime::createFromFormat('Y-m-d H:i', "$dateDepart $heureDepart");
        $dateDestinationTime = \DateTime::createFromFormat('Y-m-d H:i', "$dateDestination $heureDestination");
        $dateDepartOnly = \DateTime::createFromFormat('Y-m-d', $dateDepart);
        $dateDestinationOnly = \DateTime::createFromFormat('Y-m-d', $dateDestination);

        $errors = [];

        // On arrête les vérifications si les dates sont invalides
        if($dateDepartOnly && $dateDepartTime && $dateDestinationOnly && $dateDestinationTime) {
        } else {
            $errors[] = "Format de date ou d'heure invalide.";
            return $errors; 
        }


        // On vérifie que la date de départ n'est pas antérieure à aujourd'hui
        if($dateDepartOnly < (new \DateTime())->setTime(0, 0)) {
            $errors[] = "La date de départ ne peut pas être antérieure à aujourd'hui.";
        }
         // Départ ce jour, on vérifie que l'heure n'est pas passée
        if($dateDepartOnly->format('Y-m-d') === $now->format('Y-m-d') && $dateDepartTime->format("H:i") < $now->format("H:i")) {
            $errors[] = "L'heure de départ aujourd'hui ne peut pas être antérieure à l'heure actuelle.";
        }
        // Si destination aujourd'hui, on vérifie que l'heure n'est pas antérieure à l'heure de départ
        if($dateDestinationOnly->format('Y-m-d') === $now->format('Y-m-d') && $dateDestinationTime->format("H:i") < $dateDepartTime->format("H:i")) {
            $errors[] = "L'heure de destination aujourd'hui ne peut pas être antérieure à l'heure de départ.";
        }
        // On vérifie que la date de destination n'est pas antérieure à la date de départ
        if($dateDestinationOnly < $dateDepartOnly) {
            $errors[] = "La date de destination ne peut pas être antérieure à la date de départ.";
        }


        if($villeDepart === $villeArrivee) {
            $errors[] = "Les villes de départ et d'arrivée doivent être différentes.";
        }

        if($places <= 0) {
            $errors[] = "Le nombre de places doit être supérieur à zéro.";
        }

        return $errors;
    }

    public function createTrajet(): void
    {
        $isLogged = $this->prepare();
       if(!$isLogged) {
            header('Location: /login');
            exit;
        }
        
        $errors = $this->controleTrajet();

        if(!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['input'] = $_POST;
            header('Location: /creer');
            exit;
        }
        else {
            $villeDepart = $_POST['agence_depart'] ?? '';
            $villeArrivee = $_POST['agence_destination'] ?? '';
            $places = $_POST['places'] ?? '';
            $dateDepart = new \DateTime($_POST['date_depart'] . ' ' . $_POST['heure_depart']);
            $dateDestination = new \DateTime($_POST['date_destination'] . ' ' . $_POST['heure_destination']);
            $trajetModel = new Trajet();
            if($trajetModel->create($_SESSION['user']['id'], $dateDepart, $dateDestination, $places, $villeDepart, $villeArrivee)) {
                $_SESSION['flashMsg'] = "Trajet crée avec succès.";
                $_SESSION['input'] = $_POST;
                header('Location: /creer');
            } else {
                $_SESSION['flashMsg'] = "Erreur lors de la création du trajet : " . $trajetModel->getLastError();
                $_SESSION['input'] = $_POST;
                header('Location: /creer');
            }
            exit;
        }
    }

    public function updateTrajet(int $id): void
    {
        $isLogged = $this->prepare();
       if(!$isLogged) {
            header('Location: /login');
            exit;
        }

        $trajetModel = new Trajet();
        $trajet = $trajetModel->getById($id);
        if (!$trajet) {
            $_SESSION['flashMsg'] = "Trajet non trouvé.";
            header('Location: /');
            exit;
        }

        if ($trajet['auteur'] !== $_SESSION['user']['id']) {
            $_SESSION['flashMsg'] = "Vous n'êtes pas autorisé à modifier ce trajet.";
            header('Location: /');
            exit;
        }

        $errors = $this->controleTrajet();

        if(!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['input'] = $_POST;
            header('Location: /modifier/' . $id);
            exit;
        }
        else {
            $villeDepart = $_POST['agence_depart'] ?? '';
            $villeArrivee = $_POST['agence_destination'] ?? '';
            $places = $_POST['places'] ?? '';
            $dateDepart = new \DateTime($_POST['date_depart'] . ' ' . $_POST['heure_depart']);
            $dateDestination = new \DateTime($_POST['date_destination'] . ' ' . $_POST['heure_destination']);
            if($trajetModel->updateById($id, $_SESSION['user']['id'], $dateDepart, $dateDestination, $places, $villeDepart, $villeArrivee)) {
                $_SESSION['flashMsg'] = "Trajet modifié avec succès.";
                $_SESSION['input'] = $_POST;
                header('Location: /modifier/' . $id);
            } else {
                $_SESSION['flashMsg'] = "Erreur lors de la modification du trajet : " . $trajetModel->getLastError();
                $_SESSION['input'] = $_POST;
                header('Location: /modifier/' . $id);
            }
            exit;
        }
    }

    public function deleteTrajet(): void
    {
        $isLogged = $this->prepare();
       if(!$isLogged) {
            header('Location: /login');
            exit;
        }

        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            $_SESSION['flashMsg'] = "ID de trajet invalide.";
            header('Location: /');
            exit;
        }

        $trajetModel = new Trajet();
        $id = (int) $_POST['id'];
        $trajeta = $trajetModel->getById($id);
        if (!$trajeta) {
            $_SESSION['flashMsg'] = "Trajet non trouvé.";
            header('Location: /');
            exit;
        }

        if ($trajeta['auteur'] !== $_SESSION['user']['id'] && !Auth::isAdmin()) {
            $_SESSION['flashMsg'] = "Vous n'êtes pas autorisé à supprimer ce trajet.";
            header('Location: /');
            exit;
        }

        if ($trajetModel->deleteById($id)) {
            $_SESSION['flashMsg'] = "Trajet supprimé avec succès.";
        } else {
            $_SESSION['flashMsg'] = "Erreur lors de la suppression du trajet : " . $trajetModel->getLastError();
        }
        header('Location: /');
        exit;
    }
    /**
     * @param array<string, mixed> $data
     */
    private function json(array $data, int $status = 200): void 
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function viewTrajet(int $id): void
    {
        $isLogged = $this->prepare();
        $isAdmin = Auth::isAdmin();
       if(!$isLogged) {
            header('Location: /login');
            exit;
        }

        $trajetModel = new Trajet();
        $trajet = $trajetModel->getById($id);
        if (!$trajet) {
            $this->json(['error' => 'Trajet non trouvé'], 404);
            return;
        }

        $this->json([
            'id' => (int)$trajet['id'],
            'auteur' => htmlspecialchars($trajet['auteur_nom']) . " " . htmlspecialchars($trajet['auteur_prenom']),
            'auteur_tel' => htmlspecialchars($trajet['auteur_tel']),
            'auteur_email' => htmlspecialchars($trajet['auteur_email']),
            'places' => (int)$trajet['places']
        ]);
    }
}
