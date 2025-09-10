<?php $isLogged = $isLogged ?? ''; ?>
<?php $isAdmin = $isAdmin ?? ''; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Touche pas au klaxon' ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        TOUCHE PAS AU KLAXON
        <right>
            <?php if (!$isLogged) { ?>
                <a href="/login">Se connecter</a>
            <?php } else if ($isAdmin) { ?>
                <span>Bienvenue <?php echo " ".$_SESSION['user']['nom']." ".$_SESSION['user']['prenom']; ?></span>
                <a href="/admin">Utilisateurs</a>
                <a href="/admin/agences">Agences</a>
                <a href="/admin/trajets">Trajets</a>
                <a href="/logout">Se déconnecter</a>
            <?php } else { ?>
                <span>Bienvenue <?php echo " ".$_SESSION['user']['nom']." ".$_SESSION['user']['prenom']; ?></span>
                <a href="/creer">Créer un trajet</a>
                <a href="/logout">Se déconnecter</a>
            <?php } ?>
        </right>
    </header>
