<?php 
namespace Touchepasauklaxon\Controllers;
use Touchepasauklaxon\Models\User;

class AdminController 
{
    public function index() 
    {
        session_start();
        // Vérification si l'utilisateur est connecté et s'il est admin
        if (!isset($_SESSION['user']) || !$_SESSION['user']['isAdmin']) {
            header("Location: /");
            echo "Accès interdit. Vous devez être administrateur pour accéder à cette page.";
            exit;
        }

        $userModel = new User();
        $users = $userModel->getAll();

        // Affichage de la liste des utilisateurs
        echo "<h1>Liste des utilisateurs</h1>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['nom']) . "</td>";
            echo "<td>" . htmlspecialchars($user['prenom']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . ($user['isAdmin'] ? 'Administrateur' : 'Utilisateur') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
}