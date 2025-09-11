<?php
// Valeurs par défaut sûres
$action  = isset($action) && is_string($action) ? $action : '/add';
$mode    = isset($mode) && is_string($mode) ? $mode : 'create';
$trajet  = isset($trajet) && is_array($trajet) ? $trajet : null;
$agences = isset($agences) && is_array($agences) ? $agences : [];

// Filtre agences : on ne garde que les {id:int, ville:string}
$agences = array_values(array_filter($agences, static function ($a): bool {
    return is_array($a)
        && (isset($a['id']) && (is_int($a['id']) || (is_string($a['id']) && ctype_digit($a['id']))))
        && is_string($a['ville'] ?? null);
}));

/** Helpers **************************************************************/

/** Encode HTML seulement si string, sinon '' */
function h(?string $v): string {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

/** Extrait une string sûre depuis $source[$key] */
function fromArrayString(?array $source, string $key, string $default = ''): string {
    if (!$source) return $default;
    $v = $source[$key] ?? null;
    return is_string($v) ? $v : $default;
}

/** Convertit une date/heure (string ou DateTime) en format demandé, sinon '' */
function fmtDateTime(mixed $v, string $format): string {
    if ($v instanceof DateTimeInterface) {
        return $v->format($format);
    }
    if (is_string($v)) {
        $ts = strtotime($v);
        if ($ts !== false) return date($format, $ts);
    }
    return '';
}

/**
 * Récupère la valeur "old" pour un champ :
 * 1) $_SESSION['input'][$key] si string
 * 2) sinon depuis $trajet (avec séparation date/heure)
 * 3) sinon $default
 */
function old(string $key, ?array $trajet = null, string $default = ''): string {
    $input = isset($_SESSION['input']) && is_array($_SESSION['input']) ? $_SESSION['input'] : [];
    $sess = $input[$key] ?? null;
    if (is_string($sess)) {
        return h($sess);
    }

    if ($trajet) {
        // date_depart / date_destination -> 'Y-m-d'
        if (in_array($key, ['date_depart', 'date_destination'], true)) {
            $raw = $trajet[$key] ?? null;
            $val = fmtDateTime($raw, 'Y-m-d');
            if ($val !== '') return h($val);
        }

        // heure_depart / heure_destination -> 'H:i' (depuis date_* correspondante)
        if (in_array($key, ['heure_depart', 'heure_destination'], true)) {
            $baseKey = str_replace('heure_', 'date_', $key); // heure_* se base sur date_*
            $raw = $trajet[$baseKey] ?? null;
            $val = fmtDateTime($raw, 'H:i');
            if ($val !== '') return h($val);
        }

        // Valeur brute si string
        $raw = $trajet[$key] ?? null;
        if (is_string($raw)) return h($raw);
        // Si int et attendu comme string (ex: select value), on cast proprement
        if (is_int($raw)) return h((string)$raw);
    }

    return h($default);
}

/** ID du trajet sûr pour hidden input / URL */
$trajetId = 0;
if ($trajet) {
    $rawId = $trajet['id'] ?? null;
    if (is_int($rawId)) $trajetId = $rawId;
    elseif (is_string($rawId) && ctype_digit($rawId)) $trajetId = (int)$rawId;
}

/** Données utilisateur sûres */
$user = $_SESSION['user'] ?? null;
$userNom    = is_array($user) && is_string($user['nom']    ?? null) ? $user['nom']    : '';
$userPrenom = is_array($user) && is_string($user['prenom'] ?? null) ? $user['prenom'] : '';
$userEmail  = is_array($user) && is_string($user['email']  ?? null) ? $user['email']  : '';
$userTel    = is_array($user) && is_string($user['tel']    ?? null) ? $user['tel']    : '';

/** Version éventuelle (optimistic locking, etc.) */
$versionVal = isset($version) && (is_int($version) || (is_string($version) && ctype_digit($version)))
    ? (string)$version
    : null;
?>
<form method="post" action="<?= h($action) ?>">
    <?php if ($mode === 'edit'): ?>
        <input type="hidden" name="_method" value="PATCH">
        <input type="hidden" name="id" value="<?= $trajetId ?>">
        <?php if ($versionVal !== null): ?>
            <input type="hidden" name="version" value="<?= $versionVal ?>">
        <?php endif; ?>
    <?php endif; ?>

    <!-- Infos utilisateur (non éditables ici) -->
    <label>Nom :</label>
    <input type="text" name="nom" disabled value="<?= h($userNom) ?>">
    <br>
    <label>Prénom :</label>
    <input type="text" name="prenom" disabled value="<?= h($userPrenom) ?>">
    <br>
    <label>Email :</label>
    <input type="email" name="email" disabled value="<?= h($userEmail) ?>">
    <br>
    <label>Téléphone :</label>
    <input type="tel" name="tel" disabled value="<?= h($userTel) ?>">
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
        <?php $selectedDepart = old('agence_depart', $trajet); ?>
        <?php foreach ($agences as $ag): ?>
            <?php
            $agId   = is_int($ag['id']) ? $ag['id'] : (int)$ag['id'];
            $agVille = (string)$ag['ville']; // safe car filtré plus haut
            $isSelected = ($agVille === html_entity_decode($selectedDepart)) || ((string)$agId === html_entity_decode($selectedDepart));
            ?>
            <option value="<?= $agId ?>" <?= $isSelected ? 'selected' : '' ?>>
                <?= h($agVille) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br>

    <label for="agence_destination">Agence d'arrivée :</label>
    <select id="agence_destination" name="agence_destination" required>
        <option value="">Sélectionnez une agence</option>
        <?php $selectedArrivee = old('agence_destination', $trajet); ?>
        <?php foreach ($agences as $ag): ?>
            <?php
            $agId   = is_int($ag['id']) ? $ag['id'] : (int)$ag['id'];
            $agVille = (string)$ag['ville'];
            $isSelected = ($agVille === html_entity_decode($selectedArrivee)) || ((string)$agId === html_entity_decode($selectedArrivee));
            ?>
            <option value="<?= $agId ?>" <?= $isSelected ? 'selected' : '' ?>>
                <?= h($agVille) ?>
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
