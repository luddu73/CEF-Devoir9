<?php $isLogged = $isLogged ?? ''; ?>
<?php $isAdmin = $isAdmin ?? ''; ?>
<?php $trajetModel = $trajetModel ?? null; ?>
<?php $trajets = $trajets ?? []; ?>
<?php $title = $title ?? 'Touche pas au klaxon'; ?>

<?php 
if (!$isLogged) 
{
    echo '<h1>Pour obtenir plus d\'informations sur un trajet, veuillez vous connecter.</h1>';
}
elseif ($isAdmin) 
{
    echo '<h1>Gestion des trajets</h1>';
}
else
{
    echo '<h1>Trajets proposés</h1>';
}
?>
<table class="table w-100 m-3 text-center table-striped">
    <tr class="table-info">
        <th>Départ</th>
        <th>Date</th>
        <th>Heure</th>
        <th>Destination</th>
        <th>Date</th>
        <th>Heure</th>
        <th>Places</th>
        <?php
        if ($isLogged) {
            echo "<th>Actions</th>";
        } ?>
    </tr>
    <?php
    if(is_object($trajetModel) && method_exists($trajetModel, 'getLastError') && $trajetModel->getLastError()) {
        echo "<tr><td colspan='8'>Erreur lors de la récupération des trajets : " . htmlspecialchars($trajetModel->getLastError()) . "</td></tr>";
    } elseif (empty($trajets)) {
         echo "<tr><td colspan='8'>Aucun trajet trouvé.</td></tr>";
    } else {
        foreach ($trajets as $trajet) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($trajet['agence_depart']) . "</td>";
            echo "<td>" . $trajet['date_depart']->format('d/m/Y') . "</td>";
            echo "<td>" . $trajet['date_depart']->format('H:i') . "</td>";
            echo "<td>" . htmlspecialchars($trajet['agence_destination']) . "</td>";
            echo "<td>" . $trajet['date_destination']->format('d/m/Y') . "</td>";
            echo "<td>" . $trajet['date_destination']->format('H:i') . "</td>";
            echo "<td>" . (int)($trajet['places']) . "</td>";
            if ($isLogged) {
                echo "<td>";
                echo "<button type='button' class='btn btn-icon btn-detail' data-id='".(int)$trajet['id']."' aria-label='Détails' title='Détails'><img src='/img/eye.svg' alt='' class='icon'></button>";
                $userId = (is_array($_SESSION['user'] ?? null) && isset($_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
                if($trajet['auteur'] === $userId) {
                    echo "<a href='/modifier/" . (int)($trajet['id']) . "' style='display:inline;'>";
                    echo "<button type='button' class='btn btn-icon' value='Modifier'><img src='/img/pen.svg' alt='' class='icon'></button>";
                    echo "</a>";
                    echo "<form method='POST' action='/delete' style='display:inline;'>";
                    echo "<input type='hidden' name='id' value='" . (int)($trajet['id']) . "'>";
                    echo "<button type='submit' class='btn btn-icon' value='Supprimer'><img src='/img/trash.svg' alt='' class='icon'></button>";
                    echo "</form>";
                }
                if ($isAdmin && $trajet['auteur'] !== $userId) {
                    echo "<form method='POST' action='/admin/trajets/delete' style='display:inline;'>";
                    echo "<input type='hidden' name='id' value='" . (int)($trajet['id']) . "'>";
                    echo "<button type='submit' class='btn btn-icon' value='Supprimer'><img src='/img/trash.svg' alt='' class='icon'></button>";
                    echo "</form>";
                }
            }
            echo "</tr>";
        }
    }
    ?>
</table>