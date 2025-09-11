<?php
/** @var array<string, mixed>|null $trajet */

// --- Erreurs de formulaire, toujours un array de strings ---
$errors = $_SESSION['form_errors'] ?? [];
if (!is_array($errors)) {
    $errors = [];
}

// --- Flash message, toujours une string ---
$flashMsg = $_SESSION['flashMsg'] ?? null;
$flashMsg = is_string($flashMsg) ? $flashMsg : null;
if ($flashMsg !== null) {
    unset($_SESSION['flashMsg']);
}

// --- Action du formulaire, sûre ---
$mode   = 'create';
$action = '/add';
?>
<h1>Création d'un trajet</h1>
<?php if (!empty($errors)): ?>
    <div class="errors">
        <ul>
            <?php foreach ($errors as $error): ?>
                <?php if (is_string($error)): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['form_errors']); ?>
<?php endif; ?>

<?php if ($flashMsg !== null): ?>
    <p><center><?= htmlspecialchars($flashMsg) ?></center></p>
<?php endif; ?>

<?php
require_once dirname(__DIR__, 2) . '/app/Views/components/form-trajet.php';
unset($_SESSION['input']);


