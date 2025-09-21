<?php $isLogged = $isLogged ?? ''; ?>
<?php $isAdmin = $isAdmin ?? ''; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Touche pas au klaxon' ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="m-auto bg-light d-flex flex-column min-vh-100">
    <header class="bg-primary">
        <nav class="navbar navbar-expand-lg mx-auto">
            <div class="container-fluid text-light">
                <a class="navbar-brand text-light" href="/">Touche pas au klaxon</a>
            </div>
            <div class="container-fluid text-light">
                <div class="collapse navbar-collapse d-flex justify-content-end gap-2" id="navbarText">
                        <?php if (!$isLogged AND !$isAdmin) { ?>
                            <button class="btn btn-secondary" onclick="location.href='/login'">Connexion</button>
                        <?php } else if ($isAdmin) { ?>
                            <button class="btn btn-info" onclick="location.href='/admin'">Utilisateurs</button>
                            <button class="btn btn-info" onclick="location.href='/admin/agences'">Agences</button>
                            <button class="btn btn-info" onclick="location.href='/admin/trajets'">Trajets</button>
                        <span>Bienvenue <?php
                            $user = $_SESSION['user'] ?? null;                                
                            $prenom = '';
                            $nom = '';
                            if (is_array($user)) {
                                $prenom = is_string($user['prenom'] ?? null) ? $user['prenom'] : '';
                                $nom = is_string($user['nom'] ?? null) ? $user['nom'] : '';
                                echo ' ' . $nom . ' ' . $prenom;
                            }
                        ?></span>
                        <button class="btn btn-secondary" onclick="location.href='/logout'">Se déconnecter</button>
                    <?php } else { ?>
                        <button class="btn btn-secondary" onclick="location.href='/creer'">Créer un trajet</button>
                        <span>Bienvenue <?php
                            $user = $_SESSION['user'] ?? null;
                            $prenom = '';
                            $nom = '';
                            if (is_array($user)) {
                                $prenom = is_string($user['prenom'] ?? null) ? $user['prenom'] : '';
                                $nom = is_string($user['nom'] ?? null) ? $user['nom'] : '';
                                echo ' ' . $nom . ' ' . $prenom;
                            }
                        ?></span>
                        <button class="btn btn-secondary" onclick="location.href='/logout'">Se déconnecter</button>
                    <?php } ?>
                </div>
            </div>
        </nav>
    </header>
    <main class="container m-auto my-3 flex-fill">
        <?php 
        $flashMsg = $_SESSION['flashMsg'] ?? null;
        $flashMsgColor = $_SESSION['flashMsgColor'] ?? null;
        if (is_string($flashMsg)) 
        { 
            $flashMsgColorStr = is_string($flashMsgColor) ? $flashMsgColor : 'info';
            ?>
            <div class="alert alert-<?= htmlspecialchars($flashMsgColorStr) ?> col-9 m-auto my-2" role="alert">
                <?php echo (string)$flashMsg; ?>
            </div>
        <?php 
        unset($_SESSION['flashMsg']);
        unset($_SESSION['flashMsgColor']);
        } ?>