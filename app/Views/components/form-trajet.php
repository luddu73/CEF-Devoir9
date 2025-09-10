<?php
// On définit des valeurs par défaut si les variables ne sont pas définies
$action = $action ?? '/add';
$mode   = $mode ?? 'create';
$trajet = $trajet ?? null;
$agences = $agences ?? [];

/**
 * @param string $key
 * @param array<string, mixed>|null $trajet
 * @param string $default
 * @return string
 */
function old(string $key, ?array $trajet = null, string $default = ''): string {
    // Priorité 1 : à la saisie
    if (isset($_SESSION['input'][$key])) return htmlspecialchars($_SESSION['input'][$key]);
    // Priorité 2 : Dans le trajet, on récupère les date départ et destination pour séparer les heures de la date
    if ($trajet) {
        // On sépare la date
        if (in_array($key, ['date_depart', 'date_destination']) && isset($trajet[$key])) {
            return htmlspecialchars(date('Y-m-d', strtotime($trajet[$key])));
        }
        // On sépare l'heure
        if (in_array($key, ['heure_depart', 'heure_destination'])) {
            // on remplace les valeurs dans les input formulaires
            $baseKey = str_replace('heure_', 'date_', $key); 
            if (isset($trajet[$baseKey])) {
                return htmlspecialchars(date('H:i', strtotime($trajet[$baseKey])));
            }
        }
    }
    // Priorité 3 : Si $trajet existe
    if ($trajet && isset($trajet[$key])) return htmlspecialchars($trajet[$key]);

    return htmlspecialchars($default);
}
?>
<form method="post" action="<?= htmlspecialchars($action) ?>">
    <?php if ($mode === 'edit'): ?>
        <input type="hidden" name="_method" value="PATCH">
        <input type="hidden" name="id" value="<?= (int)$trajet['id'] ?>">
        <?php if (!empty($version)): ?>
            <input type="hidden" name="version" value="<?= (int)$version ?>">
        <?php endif; ?>
    <?php endif; ?>

    <!-- Infos utilisateur (non éditables ici) -->
    <label>Nom :</label>
    <input type="text" name="nom" disabled value="<?= htmlspecialchars($_SESSION['user']['nom'] ?? '') ?>">
    <br>
    <label>Prénom :</label>
    <input type="text" name="prenom" disabled value="<?= htmlspecialchars($_SESSION['user']['prenom'] ?? '') ?>">
    <br>
    <label>Email :</label>
    <input type="email" name="email" disabled value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>">
    <br>
    <label>Téléphone :</label>
    <input type="tel" name="tel" disabled value="<?= htmlspecialchars($_SESSION['user']['tel'] ?? '') ?>">
    <hr>

    <!-- Champs du trajet -->
    <label for="date_depart">Date de départ :</label>
    <input type="date" id="date_depart" name="date_depart" required value="<?= old('date_depart', $trajet) ?>">
    <br>
    <label for="heure_depart">Heure de départ :</label>
    <input type="time" id="heure_depart" name="heure_depart" required value="<?= old('heure_depart', $trajet) ?>">
    <br>
    <label for="date_destination">Date de destination :</label>
    <input type="date" id="date_destination" name="date_destination" required value="<?= old('date_destination', $trajet) ?>">
    <br>
    <label for="heure_destination">Heure de destination :</label>
    <input type="time" id="heure_destination" name="heure_destination" required value="<?= old('heure_destination', $trajet) ?>">
    <br>

    <label for="agence_depart">Agence de départ :</label>
    <select id="agence_depart" name="agence_depart" required>
        <option value="">Sélectionnez une agence</option>
        <?php $selectedDepart = old('agence_depart', $trajet);
        foreach ($agences as $ag): ?>
            <option value="<?= (int)$ag['id'] ?>" <?= ((string)$ag['ville'] === (string)$selectedDepart || (string)$ag['id'] === (string)$selectedDepart) ? 'selected' : '' ?>>
                <?= htmlspecialchars($ag['ville']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br>

    <label for="agence_destination">Agence d'arrivée :</label>
    <select id="agence_destination" name="agence_destination" required>
        <option value="">Sélectionnez une agence</option>
        <?php $selectedArrivee = old('agence_destination', $trajet); ?>
        <?php foreach ($agences as $ag): ?>
            <option value="<?= (int)$ag['id'] ?>" <?= ((string)$ag['ville'] === (string)$selectedArrivee || (string)$ag['id'] === (string)$selectedArrivee) ? 'selected' : '' ?>>
                <?= htmlspecialchars($ag['ville']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br>

    <label for="places">Nombre de places :</label>
    <input type="number" id="places" name="places" min="1" required value="<?= old('places', $trajet) ?>">
    <br>

    <button type="submit">
        <?= $mode === 'edit' ? 'Mettre à jour le trajet' : 'Créer le trajet' ?>
    </button>
</form>
