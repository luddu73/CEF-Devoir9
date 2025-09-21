<?php 
/**
 * Contrôleur pour la gestion de l'authentification des utilisateurs.
 *
 * Gère les actions de connexion et de déconnexion.
 *
 * @category AuthController
 * @package  TouchePasAuKlaxon
 */
namespace Touchepasauklaxon\Controllers;
use Touchepasauklaxon\Models\User;
use Touchepasauklaxon\Auth;

class AuthController 
{
    /** Gère la connexion des utilisateurs.
     *
     * @return void
     */
    public function login(): void 
    {
        session_start();
        // Si l'utilisateur est déjà connecté, on le redirige ou on affiche un message
        if(Auth::isLogged()) 
        {
            header("Location: /");
            echo "Vous êtes déjà connecté.";
            exit;
        }

        // Si formulaire soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') 
        {
            // Vérification des données du formulaire
            if (empty($_POST['email']) || empty($_POST['password'])) 
            {
                $_SESSION['flashMsg'] = "Veuillez remplir tous les champs.";
                $_SESSION['flashMsgColor'] = "warning";
                header("Location: /login");
                exit;
            }

            $email = is_string($_POST['email']) ? trim($_POST['email']) : '';
            $password = $_POST['password'];

            $userModel = new User();
            $user = $userModel->getByEmail($email);


            if($user && password_verify($password, $user['password'])) 
            {
                // Si les identifiants sont valides, on démarre la session et on stocke les informations de l'utilisateur
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom'],
                    'email' => $user['email'],
                    'tel' => $user['tel'],
                    'isAdmin' => $user['isAdmin']
                ];
                echo "Connexion réussie !";
                header("Location: /");
                exit;
            } 
            else
            {
                $_SESSION['flashMsg'] = "Identifiants invalides.";
                $_SESSION['flashMsgColor'] = "danger";
                header("Location: /login");
                exit;
            }
        }
        else
        {
            $_SESSION['flashMsg'] = "Méthode de requête non autorisée.";
            $_SESSION['flashMsgColor'] = "danger";
            header("Location: /login");
            exit;
        }
    }

    /** Gère la déconnexion des utilisateurs.
     *
     * @return void
     */
    public function logout(): void
    {
        session_start();
        session_destroy();
        header("Location: /");
        exit;
    }
}