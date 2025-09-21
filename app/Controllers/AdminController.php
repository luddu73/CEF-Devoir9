<?php 
namespace Touchepasauklaxon\Controllers;
use Touchepasauklaxon\Auth;
use Touchepasauklaxon\Models\User;
use Touchepasauklaxon\Models\Agence;
use Touchepasauklaxon\Models\Trajet;

class AdminController extends Auth
{

    private function prepare(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        Auth::requireAdmin();
    }

    public function index(): void
    {
        $this->prepare();
        $isLogged = Auth::isLogged();
        $isAdmin  = Auth::isAdmin();

        $userModel = new User();
        $users = $userModel->getAll();

        $title = 'Liste des utilisateurs - Touche pas au Klaxon';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/header.php';

        echo "<h1>Liste des utilisateurs</h1>";
        echo "<table class='table w-100 m-3 text-center table-striped'>";
        echo "<tr class='table-info'>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Rôle</th>
        </tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['nom']) . "</td>";
            echo "<td>" . htmlspecialchars($user['prenom']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['tel']) . "</td>";
            echo "<td>" . ($user['isAdmin'] == 1 ? 'Administrateur' : 'Utilisateur') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        require_once dirname(__DIR__, 2) . '/app/Views/templates/footer.php';
    }

    public function agences(): void
    {
        $this->prepare();
        $isLogged = Auth::isLogged();
        $isAdmin  = Auth::isAdmin();
        
        $title = 'Gestion des agences - Touche pas au Klaxon';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/header.php';

        echo "<h1>Gestion des agences</h1>";
        echo "<p>Cette section est réservée à la gestion des agences.</p>";
        $agenceModel = new Agence();
        $agences = $agenceModel->getAll();

        echo "<h2>Liste des agences</h2>";
        echo "<table class='table w-100 m-3 text-center table-striped'>";
        echo "<tr class='table-info'>
            <th>ID</th>
            <th>Ville</th>
            <th>Actions</th>
        </tr>";
        
        if($agenceModel->getLastError()) {
            echo "<tr><td colspan='3'>Erreur lors de la récupération des agences : " . htmlspecialchars($agenceModel->getLastError()) . "</td></tr>";
        } elseif (empty($agences)) {
            echo "<tr><td colspan='3'>Aucune agence trouvée.</td></tr>";
        } else {
            foreach ($agences as $agence) {
                echo "<tr>";
                echo "<td>" . (int)($agence['id']) . "</td>";
                echo "<td id='ville-cell-". (int)($agence['id']) ."'>";
                echo htmlspecialchars($agence['ville']);
                echo " | <button onclick='enableEdit(" . (int)($agence['id']) . ", " . json_encode($agence['ville']) . ")' class='btn btn-icon'><img src='/img/pen.svg' alt='' class='icon'></button>";
                echo "</td>";
                echo "<td>";
                echo "<form method='POST' action='/admin/agences/delete' style='display:inline;'>";
                echo "<input type='hidden' name='id' value='" . (int)($agence['id']) . "'>";
                echo "<button type='submit' class='btn btn-danger'>Supprimer</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
        }
        echo "</table>
            <hr>";
        echo "<h2>Ajouter une nouvelle agence</h2>";
        echo "<form method='POST' action='/admin/agences/add'>";
        echo '<div class="mb-3">';
        echo "<label class='form-label' for='ville'>Ville : </label>";
        echo "<input class='form-control' type='text' id='ville' name='ville' required>";
        echo "</div>";
        echo "<div class='mb-3'>";
        echo "<button class='btn btn-primary' type='submit' value='Ajouter'>Ajouter</button>";
        echo "</div>";
        echo "</form>";
        ?>
        <script>
            function enableEdit(id, ville) {
                const cell = document.getElementById('ville-cell-' + id);

                cell.innerHTML = `
                    <form method="POST" action="/admin/agences/edit" style="display:inline;">
                        <input type="hidden" name="id" value="${id}">
                        <div class="d-flex row justify-content-center">
                            <div class="col-6 text-center m-0">
                                <input type="text" class="form-control" name="ville" value="${ville}" required>
                            </div>
                            <div class="col-6 text-center m-0">
                                <button type="submit" class="btn btn-primary">Valider</button>
                            </div>
                        </div>
                    </form>
                `;
            }
        </script>
        <?php 
        require_once dirname(__DIR__, 2) . '/app/Views/templates/footer.php';
    }

    public function addAgence(): void
    {
        $this->prepare();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ville = $_POST['ville'] ?? null;
            $ville = is_string($ville) ? trim($ville) : '';
            if (!empty($ville)) {
                $agenceModel = new Agence();
                if ($agenceModel->add($ville)) {
                    $_SESSION['flashMsg'] = "Agence ajoutée avec succès.";
                    $_SESSION['flashMsgColor'] = "success";
                } else {
                    $_SESSION['flashMsg'] = "Erreur lors de l'ajout de l'agence : " . $agenceModel->getLastError();
                    $_SESSION['flashMsgColor'] = "danger";
                }
            } else {
                $_SESSION['flashMsg'] = "Veuillez remplir le champ ville.";
                $_SESSION['flashMsgColor'] = "warning";
            }
        } else {
            $_SESSION['flashMsg'] = "Méthode de requête non autorisée.";
            $_SESSION['flashMsgColor'] = "danger";
        }
        header("Location: /admin/agences");
        exit;
    }

    public function editAgence(): void
    {
        $this->prepare();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ville = $_POST['ville'] ?? null;
            $ville = is_string($ville) ? trim($ville) : '';
            if (!empty($ville)) {
                $agenceModel = new Agence();
                $id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int) $_POST['id'] : 0;
                if ($agenceModel->updateById($id, $ville)) {
                    $_SESSION['flashMsg'] = "Agence modifiée avec succès.";
                    $_SESSION['flashMsgColor'] = "success";
                } else {
                    $_SESSION['flashMsg'] = "Erreur lors de la modification de l'agence : " . $agenceModel->getLastError();
                    $_SESSION['flashMsgColor'] = "danger";
                }
            } else {
                $_SESSION['flashMsg'] = "Veuillez remplir le champ ville.";
                $_SESSION['flashMsgColor'] = "warning";
            }
        } else {
            $_SESSION['flashMsg'] = "Méthode de requête non autorisée.";
            $_SESSION['flashMsgColor'] = "danger";
        }
        header("Location: /admin/agences");
        exit;
    }

    public function deleteAgence(): void
    {
        $this->prepare();

        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            $_SESSION['flashMsg'] = "ID d'agence invalide.";
            $_SESSION['flashMsgColor'] = "danger";
            header("Location: /admin/agences");
            exit;
        }

        $id = (int) $_POST['id'];

        $agenceModel = new Agence();
        if ($agenceModel->deleteById($id)) {
            $_SESSION['flashMsg'] = "Agence supprimée avec succès.";
            $_SESSION['flashMsgColor'] = "success";
        } else {
            $_SESSION['flashMsg'] = "Erreur lors de la suppression de l'agence : " . $agenceModel->getLastError();
            $_SESSION['flashMsgColor'] = "danger";
        }
        header("Location: /admin/agences");
        exit;
    }

    public function trajets(): void
    {
        $this->prepare();
        $isLogged = Auth::isLogged();
        $isAdmin  = Auth::isAdmin();
        $viewAdmin = true;
/*
        $flashMsg = $_SESSION['flashMsg'] ?? null;
        if (is_string($flashMsg)) {
            echo "<p><center>" . htmlspecialchars($flashMsg) . "</center></p>";
            unset($_SESSION['flashMsg']);
        }*/

        $title = 'Liste des trajets - Touche pas au Klaxon';
        require_once dirname(__DIR__, 2) . '/app/Views/templates/header.php';
        
        $trajetModel = new Trajet();
        $trajets = $trajetModel->getAll();

        require_once dirname(__DIR__, 2) . '/app/Views/index.php';
        require_once dirname(__DIR__, 2) . '/app/Views/components/modale.php';

        require_once dirname(__DIR__, 2) . '/app/Views/templates/footer.php';
    }

    public function deleteTrajet(): void
    {
        $this->prepare();

        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            $_SESSION['flashMsg'] = "ID de trajet invalide.";
            $_SESSION['flashMsgColor'] = "danger";
            header("Location: /admin/trajets");
            exit;
        }

        $id = (int) $_POST['id'];

        $trajetModel = new Trajet();
        if ($trajetModel->deleteById($id)) {
            $_SESSION['flashMsg'] = "Trajet supprimé avec succès.";
            $_SESSION['flashMsgColor'] = "success";
        } else {
            $_SESSION['flashMsg'] = "Erreur lors de la suppression du trajet : " . $trajetModel->getLastError();
            $_SESSION['flashMsgColor'] = "danger";
        }
        header("Location: /admin/trajets");
        exit;
    }
}