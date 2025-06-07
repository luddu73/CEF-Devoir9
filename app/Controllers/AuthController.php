<?php 
namespace Touchepasauklaxon\Controllers;
use Touchepasauklaxon\Models\User;
use Touchepasauklaxon\Auth;

class AuthController 
{
    public function login() 
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
                echo "Veuillez remplir tous les champs.";
                exit;
            }

            $email = trim($_POST['email']);
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
                echo "Identifiants invalides.";
                exit;
            }
        }
        else
        {
            echo "Méthode de requête non autorisée.";
            exit;
        }
    }


    public function logout()
    {
        session_start();
        session_destroy();
        header("Location: /");
        exit;
    }
}