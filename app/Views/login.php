<?php /** @var string $title */ ?>
<h1><?= $title ?></h1>
<p>Bienvenue sur Touche pas au Klaxon !</p>
<form method="post" action="/login">
    <div class="mb-3">
        <label for="email" class="form-label">Email :</label>
        <input type="email" id="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Mot de passe :</label>
        <input type="password" id="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
        <button type="submit" class="btn btn-primary">Se connecter</button>
    </div>
</form>
