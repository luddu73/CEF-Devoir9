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
else
{
    echo '<h1>Trajets proposés</h1>';
}
?>
<table class="w-100 m-3 text-center" border='1'>
    <tr>
        <th>ID</th>
        <th>Auteur</th>
        <th>Date de départ</th>
        <th>Date de destination</th>
        <th>Ville de départ</th>
        <th>Ville d'arrivée</th>
        <th>Nombre de places</th>
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
            echo "<td>" . (int)($trajet['id']) . "</td>";
            echo "<td>" . htmlspecialchars($trajet['auteur_nom']) . " " . htmlspecialchars($trajet['auteur_prenom']) . "</td>";
            echo "<td>" . htmlspecialchars($trajet['date_depart']) . "</td>";
            echo "<td>" . htmlspecialchars($trajet['date_destination']) . "</td>";
            echo "<td>" . htmlspecialchars($trajet['agence_depart']) . "</td>";
            echo "<td>" . htmlspecialchars($trajet['agence_destination']) . "</td>";
            echo "<td>" . (int)($trajet['places']) . "</td>";
            if ($isLogged) {
                echo "<td>";
                echo "<a href='/detail/" . (int)($trajet['id']) . "' style='display:inline;'>";
                echo "<input type='button' value='Details'>";
                echo "</a>";
                $userId = (is_array($_SESSION['user'] ?? null) && isset($_SESSION['user']['id'])) ? $_SESSION['user']['id'] : null;
                if($trajet['auteur'] === $userId) {
                    echo "<a href='/modifier/" . (int)($trajet['id']) . "' style='display:inline;'>";
                    echo "<input type='button' value='Modifier'>";
                    echo "</a>";
                    echo "<form method='POST' action='/delete' style='display:inline;'>";
                    echo "<input type='hidden' name='id' value='" . (int)($trajet['id']) . "'>";
                    echo "<input type='submit' value='Supprimer'>";
                    echo "</form>";
                }
            }
            echo "</tr>";
        }
    }
    ?>
</table>