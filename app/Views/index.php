<h1><?= $title ?></h1>
<p>Bienvenue sur Touche pas au Klaxon !</p>
<form method="post" action="/login">
    <label for="email">Email :</label>
    <input type="email" id="email" name="email" required>
    <br>
    <label for="password">Mot de passe :</label>
    <input type="password" id="password" name="password" required>
    <br>
    <button type="submit">Se connecter</button>
</form>
