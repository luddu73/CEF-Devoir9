<?php
/**
 * Contrôleur pour la gestion des pages principales de l'application.
 * 
 * Gère l'affichage de la page d'accueil, des formulaires de création et
 * de modification de trajets, ainsi que les actions associées.
 * 
 * @category HomeController
 * @package  TouchePasAuKlaxon
 */
namespace Touchepasauklaxon\Controllers;

use Touchepasauklaxon\Auth;
use Touchepasauklaxon\Models\User;
use Touchepasauklaxon\Models\Trajet;
use Touchepasauklaxon\Models\Agence;

class HomeController
{
    /**
     * Prépare l'environnement pour les actions nécessitant une authentification.
     *
     * @return bool Vrai si l'utilisateur est connecté, sinon faux.
     */
    private function prepare(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return Auth::isLogged();
    }

    /**
     * Affiche la page d'accueil avec la liste des trajets.
     * 
     * @return void
     */
    public function index(): void
    {
        $isLogged = $this->prepare();
        $isAdmin = Auth::isAdmin();
        $title = 'Accueil - Touche pas au Klaxon';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/header.php';
        $trajetModel = new Trajet();
        $trajets = $trajetModel->getAccueil();
        require_once dirname(__DIR__, 2) . '/app/Views/index.php';
        require_once dirname(__DIR__, 2) . '/app/Views/components/modale.php';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/footer.php';
    }

    /** Affiche la page de connexion.
     *
     * @return void
     */
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

    /** Affiche le formulaire de création d'un trajet.
     *
     * @return void
     */
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

    /** Affiche le formulaire de modification d'un trajet.
     *
     * @param int $id ID du trajet à modifier.
     * 
     * @return void
     */
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
            $_SESSION['flashMsgColor'] = "danger";
            header('Location: /');
            exit;
        }

        $userId = (is_array($_SESSION['user'] ?? null) && isset($_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
        if ($trajet['auteur'] !== $userId) {
            $_SESSION['flashMsg'] = "Vous n'êtes pas autorisé à modifier ce trajet.";
            $_SESSION['flashMsgColor'] = "danger";
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
     * Contrôle les données du formulaire de création ou modification de trajet.
     * 
     * @return array<int, string>
     */
    private function controleTrajet(): array
    {
        $dateDepart = $_POST['date_depart'] ?? '';
        if (!is_string($dateDepart)) {
            $dateDepart = '';
        }
        $dateDestination = $_POST['date_destination'] ?? '';
        if (!is_string($dateDestination)) {
            $dateDestination = '';
        }
        $villeDepart = $_POST['agence_depart'] ?? '';
        $villeArrivee = $_POST['agence_destination'] ?? '';
        $heureDepart = $_POST['heure_depart'] ?? '';
        if (!is_string($heureDepart)) {
            $heureDepart = '';
        }
        $heureDestination = $_POST['heure_destination'] ?? '';
        if (!is_string($heureDestination)) {
            $heureDestination = '';
        }
        $places = $_POST['places'] ?? '';
        $places_disponibles = $_POST['places_disponibles'] ?? '';
        $mode = $_POST['_method'] ?? '';

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

        if($places_disponibles < 0 AND $mode == 'PATCH') {
            $errors[] = "Le nombre de places disponibles ne peut pas être négatif.";
        }

        return $errors;
    }

    /** Gère la création d'un nouveau trajet.
     *
     * @return void
     */
    public function createTrajet(): void
    {
        $isLogged = $this->prepare();
       if(!$isLogged) {
            header('Location: /login');
            exit;
        }
        
        $errors = $this->controleTrajet();

        if(!empty($errors)) {
            $_SESSION['flashMsg'] = '<ul class="mb-0">';
            foreach ($errors as $msg) {
                $_SESSION['flashMsg'] .= '<li>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</li>';
            }
            $_SESSION['flashMsg'] .= '</ul>';
            $_SESSION['input'] = $_POST;
            $_SESSION['flashMsgColor'] = "warning";
            header('Location: /creer');
            exit;
        }
        else {
            $dateDepart = $_POST['date_depart'] ?? '';
            if (!is_string($dateDepart)) {
                $dateDepart = '';
            }
            $dateDestination = $_POST['date_destination'] ?? '';
            if (!is_string($dateDestination)) {
                $dateDestination = '';
            }
            $heureDepart = $_POST['heure_depart'] ?? '';
            if (!is_string($heureDepart)) {
                $heureDepart = '';
            }
            $heureDestination = $_POST['heure_destination'] ?? '';
            if (!is_string($heureDestination)) {
                $heureDestination = '';
            }
            $villeDepart = $_POST['agence_depart'] ?? '';
            $villeArrivee = $_POST['agence_destination'] ?? '';
            $places = $_POST['places'] ?? '';
            $dateDepart = new \DateTime($dateDepart . ' ' . $heureDepart);
            $dateDestination = new \DateTime($dateDestination . ' ' . $heureDestination);
            $trajetModel = new Trajet();
            $userId = (is_array($_SESSION['user'] ?? null) && isset($_SESSION['user']['id']) && is_numeric($_SESSION['user']['id'])) ? (int)$_SESSION['user']['id'] : 0;
            $places = (is_numeric($places)) ? (int)$places : 0;
            $villeDepart = (is_numeric($villeDepart)) ? (int)$villeDepart : 0;
            $villeArrivee = (is_numeric($villeArrivee)) ? (int)$villeArrivee : 0;
            if($trajetModel->create($userId, $dateDepart, $dateDestination, $places, $villeDepart, $villeArrivee)) {
                $_SESSION['flashMsg'] = "Trajet crée avec succès.";
                $_SESSION['flashMsgColor'] = "success";
                $_SESSION['input'] = $_POST;
                header('Location: /');
            } else {
                $_SESSION['flashMsg'] = "Erreur lors de la création du trajet : " . $trajetModel->getLastError();
                $_SESSION['flashMsgColor'] = "danger";
                $_SESSION['input'] = $_POST;
                header('Location: /creer');
            }
            exit;
        }
    }

    /** Gère la mise à jour d'un trajet existant.
     *
     * @param int $id ID du trajet à modifier.
     * 
     * @return void
     */
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
            $_SESSION['flashMsgColor'] = "danger";
            header('Location: /');
            exit;
        }

        $userId = (is_array($_SESSION['user'] ?? null) && isset($_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
        if($trajet['auteur'] !== $userId) {
            $_SESSION['flashMsg'] = "Vous n'êtes pas autorisé à modifier ce trajet.";
            $_SESSION['flashMsgColor'] = "danger";
            header('Location: /');
            exit;
        }

        $errors = $this->controleTrajet();

        if(!empty($errors)) {
            $_SESSION['flashMsg'] = '<ul class="mb-0">';
            foreach ($errors as $msg) {
                $_SESSION['flashMsg'] .= '<li>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</li>';
            }
            $_SESSION['flashMsg'] .= '</ul>';
            $_SESSION['input'] = $_POST;
            $_SESSION['flashMsgColor'] = "warning";
            header('Location: /modifier/' . $id);
            exit;
        }
        else {
            $villeDepart = $_POST['agence_depart'] ?? '';
            $villeArrivee = $_POST['agence_destination'] ?? '';
            $places = $_POST['places'] ?? '';
            $places_disponibles = $_POST['places_disponibles'] ?? '';
            $dateDepart = $_POST['date_depart'] ?? '';
            if (!is_string($dateDepart)) {
                $dateDepart = '';
            }
            $dateDestination = $_POST['date_destination'] ?? '';
            if (!is_string($dateDestination)) {
                $dateDestination = '';
            }
            $heureDepart = $_POST['heure_depart'] ?? '';
            if (!is_string($heureDepart)) {
                $heureDepart = '';
            }
            $heureDestination = $_POST['heure_destination'] ?? '';
            if (!is_string($heureDestination)) {
                $heureDestination = '';
            }
            $dateDepart = new \DateTime($dateDepart . ' ' . $heureDepart);
            $dateDestination = new \DateTime($dateDestination . ' ' . $heureDestination);
            $userId = (is_array($_SESSION['user'] ?? null) && isset($_SESSION['user']['id']) && is_numeric($_SESSION['user']['id'])) ? (int)$_SESSION['user']['id'] : 0;
            $places = (is_numeric($places)) ? (int)$places : 0;
            $places_disponibles = (is_numeric($places_disponibles)) ? (int)$places_disponibles : 0;
            $villeDepart = (is_numeric($villeDepart)) ? (int)$villeDepart : 0;
            $villeArrivee = (is_numeric($villeArrivee)) ? (int)$villeArrivee : 0;
            if($trajetModel->updateById($id, $userId, $dateDepart, $dateDestination, $places, $places_disponibles, $villeDepart, $villeArrivee)) {
                $_SESSION['flashMsg'] = "Trajet modifié avec succès.";
                $_SESSION['flashMsgColor'] = "success";
                $_SESSION['input'] = $_POST;
                header('Location: /');
            } else {
                $_SESSION['flashMsg'] = "Erreur lors de la modification du trajet : " . $trajetModel->getLastError();
                $_SESSION['flashMsgColor'] = "danger";
                $_SESSION['input'] = $_POST;
                header('Location: /modifier/' . $id);
            }
            exit;
        }
    }

    /** Gère la suppression d'un trajet.
     *
     * @return void
     */
    public function deleteTrajet(): void
    {
        $isLogged = $this->prepare();
       if(!$isLogged) {
            header('Location: /login');
            exit;
        }

        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            $_SESSION['flashMsg'] = "ID de trajet invalide.";
            $_SESSION['flashMsgColor'] = "danger";
            header('Location: /');
            exit;
        }

        $trajetModel = new Trajet();
        $id = (int) $_POST['id'];
        $trajeta = $trajetModel->getById($id);
        if (!$trajeta) {
            $_SESSION['flashMsg'] = "Trajet non trouvé.";
            $_SESSION['flashMsgColor'] = "danger";
            header('Location: /');
            exit;
        }

        $userId = (is_array($_SESSION['user'] ?? null) && isset($_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
        if ($trajeta['auteur'] !== $userId && !Auth::isAdmin()) {
            $_SESSION['flashMsg'] = "Vous n'êtes pas autorisé à supprimer ce trajet.";
            $_SESSION['flashMsgColor'] = "danger";
            header('Location: /');
            exit;
        }

        if ($trajetModel->deleteById($id)) {
            $_SESSION['flashMsg'] = "Trajet supprimé avec succès.";
            $_SESSION['flashMsgColor'] = "success";
        } else {
            $_SESSION['flashMsg'] = "Erreur lors de la suppression du trajet : " . $trajetModel->getLastError();
            $_SESSION['flashMsgColor'] = "danger";
        }
        header('Location: /');
        exit;
    }
    /**
     * Envoie une réponse JSON avec le code de statut HTTP spécifié.
     * 
     * @param array<string, mixed> $data
     */
    private function json(array $data, int $status = 200): void 
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Fournit les détails d'un trajet en JSON pour la modale.
     *
     * @param int $id ID du trajet à afficher.
     * 
     * @return void
     */
    public function viewTrajet(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $isLogged = $this->prepare();
        $isAdmin = Auth::isAdmin();
        if(!$isLogged) {
            $this->json(['error' => 'Non autorisé'], 401);
            return;
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
