<h1>Modification du trajet #<?= (int)$trajet['id'] ?></h1>
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
<?php
$mode    = 'edit';
$action  = '/update/' . (int)$trajet['id'].'';
require_once dirname(__DIR__, 2) . '/app/Views/components/form-trajet.php';
unset($_SESSION['input']);
