<h1>Création d'un trajet</h1>
<?php if (!empty($_SESSION['form_errors'])): ?>
    <div class="errors">
        <ul>
            <?php foreach ($_SESSION['form_errors'] as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['form_errors']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['flashMsg'])) {
            echo "<p><center>" . htmlspecialchars($_SESSION['flashMsg']) . "</center></p>";
            unset($_SESSION['flashMsg']);
        }   ?>


<?php echo date("Y-m-d H:i:s") . "<br>"; ?>
<form method="post" action="/add">
    <label for="nom">Nom :</label>
    <input type="text" id="nom" name="nom" required disabled value="<?= htmlspecialchars($_SESSION['user']['nom'] ?? '') ?>">
    <br>
    <label for="prenom">Prénom :</label>
    <input type="text" id="prenom" name="prenom" required disabled value="<?= htmlspecialchars($_SESSION['user']['prenom'] ?? '') ?>">
    <br>
    <label for="email">Email :</label>
    <input type="email" id="email" name="email" required disabled value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>">
    <br>
    <label for="tel">Téléphone :</label>
    <input type="tel" id="tel" name="tel" required disabled value="<?= htmlspecialchars($_SESSION['user']['tel'] ?? '') ?>">
    <hr>
    <label for="date_depart">Date de départ :</label>
    <input type="date" id="date_depart" name="date_depart" required value="<?= htmlspecialchars($_SESSION['input']['date_depart'] ?? '') ?>">
    <br>
    <label for="heure_depart">Heure de départ :</label>
    <input type="time" id="heure_depart" name="heure_depart" required value="<?= htmlspecialchars($_SESSION['input']['heure_depart'] ?? '') ?>">
    <br>
    <label for="date_destination">Date de destination :</label>
    <input type="date" id="date_destination" name="date_destination" required value="<?= htmlspecialchars($_SESSION['input']['date_destination'] ?? '') ?>">
    <br>
    <label for="heure_destination">Heure de destination :</label>
    <input type="time" id="heure_destination" name="heure_destination" required value="<?= htmlspecialchars($_SESSION['input']['heure_destination'] ?? '') ?>">
    <br>
    <label for="ville_depart">Agence de départ :</label>
    <select id="ville_depart" name="ville_depart" required>
        <option value="">Sélectionnez une agence</option>
        <?php foreach ($agences as $agence): ?>
            <option value="<?= htmlspecialchars($agence['id']) ?>" <?= (htmlspecialchars($agence['id']) == htmlspecialchars($_SESSION['input']['ville_depart'] ?? '')) ? 'selected' : '' ?>><?= htmlspecialchars($agence['ville']) ?></option>
        <?php endforeach; ?>
    </select>
    <br>
    <label for="ville_arrivee">Agence d'arrivée :</label>
    <select id="ville_arrivee" name="ville_arrivee" required>
        <option value="">Sélectionnez une agence</option>
        <?php foreach ($agences as $agence): ?>
            <option value="<?= htmlspecialchars($agence['id']) ?>" <?= (htmlspecialchars($agence['id']) == htmlspecialchars($_SESSION['input']['ville_arrivee'] ?? '')) ? 'selected' : '' ?>><?= htmlspecialchars($agence['ville']) ?></option>
        <?php endforeach; ?>
    </select>
    <br>
    <label for="places">Nombre de places :</label>
    <input type="number" id="places" name="places" required min="1" value="<?= htmlspecialchars($_SESSION['input']['places'] ?? '') ?>">
    <br>
    <button type="submit">Créer le trajet</button>
</form>
<?php unset($_SESSION['input']); ?>