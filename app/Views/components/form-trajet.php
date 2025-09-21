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
/**
 * @param array<string, mixed>|null $source
 */
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
 *
 * @param array<string, mixed>|null $trajet
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

?>
<form method="post" action="<?= h($action) ?>">
    <?php if ($mode === 'edit'): ?>
        <input type="hidden" name="_method" value="PATCH">
        <input type="hidden" name="id" value="<?= $trajetId ?>">
    <?php endif; ?>
    <!-- Champs utilisateur (non modifiables ici) -->
    <h2>Informations utilisateur</h2>
    <div class="mb-3">
        <label for="nom" class="form-label">Nom</label>
        <input type="text" name="nom" class="form-control" id="inputNom"  disabled value="<?= h($userNom) ?>">
    </div>
    <div class="mb-3">
        <label for="prenom" class="form-label">Prénom</label>
        <input type="text" name="prenom" class="form-control" id="inputPrenom" disabled value="<?= h($userPrenom) ?>">
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" class="form-control" id="inputEmail" disabled value="<?= h($userEmail) ?>">
    </div>
    <div class="mb-3">
        <label for="tel" class="form-label">Téléphone</label>
        <input type="text" name="tel" class="form-control" id="inputTel" disabled value="<?= h($userTel) ?>">
    </div>
    <hr>
    <!-- Champs du trajet -->
    <h2>Informations trajet</h2>
    <div class="d-flex flex-wrap justify-content-between">
        <div class="col-5">
            <div class="mb-3">
                <label for="date_depart" class="form-label">Date de départ</label>
                <input type="date" name="date_depart" class="form-control" id="inputDateDepart" value="<?= old('date_depart', $trajet) ?>">
            </div>
            <div class="mb-3">
                <label for="heure_depart" class="form-label">Heure de départ</label>
                <input type="time" name="heure_depart" class="form-control" id="inputHeureDepart" value="<?= old('heure_depart', $trajet) ?>">
            </div>
        </div>
        <div class="col-5">
            <div class="mb-3">
                <label for="date_destination" class="form-label">Date de destination</label>
                <input type="date" name="date_destination" class="form-control" id="inputDateDestination" value="<?= old('date_destination', $trajet) ?>">
            </div>
            <div class="mb-3">
                <label for="heure_destination" class="form-label">Heure de destination</label>
                <input type="time" name="heure_destination" class="form-control" id="inputHeureDestination" value="<?= old('heure_destination', $trajet) ?>">
            </div>
        </div>
        <div class="col-5">
            <div class="mb-3">
                <label for="agence_depart" class="form-label">Agence de départ</label>
                <select id="agence_depart" name="agence_depart" class="form-select" required>
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
            </div>
        </div>
        <div class="col-5">
            <div class="mb-3">
                <label for="agence_destination" class="form-label">Agence de destination</label>
                <select id="agence_destination" name="agence_destination" class="form-select" required>
                    <option value="">Sélectionnez une agence</option>
                    <?php $selectedArrivee = old('agence_destination', $trajet); ?>
                    <?php foreach ($agences as $ag): ?>
                        <?php
                        $agId   = is_int($ag['id']) ? $ag['id'] : (int)$ag['id'];
                        $agVille = (string)$ag['ville']; // safe car filtré plus haut
                        $isSelected = ($agVille === html_entity_decode($selectedArrivee)) || ((string)$agId === html_entity_decode($selectedArrivee));
                        ?>
                        <option value="<?= $agId ?>" <?= $isSelected ? 'selected' : '' ?>>
                            <?= h($agVille) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <label for="places" class="form-label">Nombre de places</label>
        <input type="number" name="places" class="form-control" id="inputPlaces" value="<?= old('places', $trajet) ?>">
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-primary">
            <?= $mode === 'edit' ? 'Mettre à jour le trajet' : 'Créer le trajet' ?>
        </button>
    </div>
</form>
