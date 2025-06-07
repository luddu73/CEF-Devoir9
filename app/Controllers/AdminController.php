<?php 
namespace Touchepasauklaxon\Controllers;
use Touchepasauklaxon\Auth;
use Touchepasauklaxon\Models\User;
use Touchepasauklaxon\Models\Agence;
use Touchepasauklaxon\Models\Trajet;

class AdminController extends Auth
{

    private function prepare()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return Auth::requireAdmin();
    }

    public function index() 
    {
        $this->prepare();
        $userModel = new User();
        $users = $userModel->getAll();

        echo "<h1>Liste des utilisateurs</h1>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Téléphone</th><th>Rôle</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['nom']) . "</td>";
            echo "<td>" . htmlspecialchars($user['prenom']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['tel']) . "</td>";
            echo "<td>" . ($user['isAdmin'] ? 'Administrateur' : 'Utilisateur') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }

    public function agences()
    {
        $this->prepare();
        echo "<h1>Gestion des agences</h1>";
        echo "<p>Cette section est réservée à la gestion des agences.</p>";
        if (isset($_SESSION['flashMsg'])) {
            echo "<p><center>" . htmlspecialchars($_SESSION['flashMsg']) . "</center></p>";
            unset($_SESSION['flashMsg']);
        }
        $agenceModel = new Agence();
        $agences = $agenceModel->getAll();

        echo "<h2>Liste des agences</h2>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Ville</th><th>Actions</th></tr>";
        
        if($agenceModel->getLastError()) {
            echo "<tr><td colspan='3'>Erreur lors de la récupération des agences : " . htmlspecialchars($agenceModel->getLastError()) . "</td></tr>";
        } elseif (empty($agences)) {
            echo "<tr><td colspan='3'>Aucune agence trouvée.</td></tr>";
        } else {
            foreach ($agences as $agence) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($agence['id']) . "</td>";
                echo "<td id='ville-cell-". htmlspecialchars($agence['id']) ."'>";
                echo htmlspecialchars($agence['ville']);
                echo " | <button onclick='enableEdit(" . htmlspecialchars($agence['id']) . ", " . json_encode($agence['ville']) . ")'>Modifier</button>";
                echo "</td>";
                echo "<td>";
                echo "<form method='POST' action='/admin/agences/delete' style='display:inline;'>";
                echo "<input type='hidden' name='id' value='" . htmlspecialchars($agence['id']) . "'>";
                echo "<input type='submit' value='Supprimer'>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
        echo "<h2>Ajouter une nouvelle agence</h2>";
        echo "<form method='POST' action='/admin/agences/add'>";
        echo "<label for='ville'>Ville : </label>";
        echo "<input type='text' id='ville' name='ville' required>";
        echo "<input type='submit' value='Ajouter'>";
        echo "</form>";
        ?>
        <script>
            function enableEdit(id, ville) {
                const cell = document.getElementById('ville-cell-' + id);

                cell.innerHTML = `
                    <form method="POST" action="/admin/agences/edit" style="display:inline;">
                        <input type="hidden" name="id" value="${id}">
                        <input type="text" name="ville" value="${ville}" required>
                        <input type="submit" value="Valider">
                    </form>
                `;
            }
        </script>
        <?php 
    }

    public function addAgence()
    {
        $this->prepare();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ville = trim($_POST['ville']);
            if (!empty($ville)) {
                $agenceModel = new Agence();
                if ($agenceModel->add($ville)) {
                    $_SESSION['flashMsg'] = "Agence ajoutée avec succès.";
                } else {
                    $_SESSION['flashMsg'] = "Erreur lors de l'ajout de l'agence : " . $agenceModel->getLastError();
                }
            } else {
                $_SESSION['flashMsg'] = "Veuillez remplir le champ ville.";
            }
        } else {
            $_SESSION['flashMsg'] = "Méthode de requête non autorisée.";
        }
        header("Location: /admin/agences");
        exit;
    }

    public function editAgence()
    {
        $this->prepare();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ville = trim($_POST['ville']);
            if (!empty($ville)) {
                $agenceModel = new Agence();
                if ($agenceModel->updateById($_POST['id'], $ville)) {
                    $_SESSION['flashMsg'] = "Agence modifiée avec succès.";
                } else {
                    $_SESSION['flashMsg'] = "Erreur lors de la modification de l'agence : " . $agenceModel->getLastError();
                }
            } else {
                $_SESSION['flashMsg'] = "Veuillez remplir le champ ville.";
            }
        } else {
            $_SESSION['flashMsg'] = "Méthode de requête non autorisée.";
        }
        header("Location: /admin/agences");
        exit;
    }

    public function deleteAgence()
    {
        $this->prepare();

        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            $_SESSION['flashMsg'] = "ID d'agence invalide.";
            header("Location: /admin/agences");
            exit;
        }

        $agenceModel = new Agence();
        if ($agenceModel->deleteById($_POST['id'])) {
            $_SESSION['flashMsg'] = "Agence supprimée avec succès.";
        } else {
            $_SESSION['flashMsg'] = "Erreur lors de la suppression de l'agence : " . $agenceModel->getLastError();
        }
        header("Location: /admin/agences");
        exit;
    }

    public function trajets()
    {
        $this->prepare();

        echo "<h1>Gestion des trajets</h1>";
        echo "<p>Cette section est réservée à la gestion des trajets.</p>";
        if (isset($_SESSION['flashMsg'])) {
            echo "<p><center>" . htmlspecialchars($_SESSION['flashMsg']) . "</center></p>";
            unset($_SESSION['flashMsg']);
        }

        $trajetModel = new Trajet();
        $trajets = $trajetModel->getAll();

        echo "<h2>Liste des trajets</h2>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Auteur</th><th>Date de départ</th><th>Date de destination</th><th>Ville de départ</th><th>Ville d'arrivée</th><th>Nombre de places</th><th>Actions</th></tr>";
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
                echo "<td>";
                echo "<form method='POST' action='/admin/trajets/delete' style='display:inline;'>";
                echo "<input type='hidden' name='id' value='" . htmlspecialchars($trajet['id']) . "'>";
                echo "<input type='submit' value='Supprimer'>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    }

    public function deleteTrajet()
    {
        $this->prepare();

        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            $_SESSION['flashMsg'] = "ID de trajet invalide.";
            header("Location: /admin/trajets");
            exit;
        }

        $trajetModel = new Trajet();
        if ($trajetModel->deleteById($_POST['id'])) {
            $_SESSION['flashMsg'] = "Trajet supprimé avec succès.";
        } else {
            $_SESSION['flashMsg'] = "Erreur lors de la suppression du trajet : " . $trajetModel->getLastError();
        }
        header("Location: /admin/trajets");
        exit;
    }
}