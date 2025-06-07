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
            <?php } else { ?>
                <span>Bienvenue <?php echo " ".$_SESSION['user']['nom']." ".$_SESSION['user']['prenom']; ?></span>
            <a href="/logout">Se déconnecter</a>
            <?php } ?>
        </right>
    </header>
