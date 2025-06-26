<?php
require 'auth.php';
require 'db.php';

$message = '';
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'validee':
            $message = "✅ La série a été validée avec succès.";
            break;
        case 'invalidee':
            $message = "❌ La série a été rejetée. L'auteur devra la modifier avant de pouvoir la revalider.";
            break;
        case 'deja_validee':
            $message = "ℹ️ Cette série est déjà validée.";
            break;
        case 'deja_invalidee':
            $message = "ℹ️ Cette série est déjà rejetée.";
            break;
        case 'serie_invalidee_non_modifiable':
            $message = "⚠️ Cette série a été rejetée et ne peut être validée qu'après modification par l'auteur.";
            break;
        case 'action_inconnue':
            $message = "❗ Action inconnue.";
            break;
        case 'serie_supprimee':
            $message = "🗑️ La série a bien été supprimée.";
            break;
        default:
            $message = "❗ Action inconnue.";
    }
}

// Récupérer toutes les séries avec nom de l'auteur
$stmt = $pdo->query("
    SELECT s.*, u.prenom, u.nom AS nom_user, u.id as id_orthophoniste
    FROM series s
    LEFT JOIN users_series us ON us.serie_id = s.id
    LEFT JOIN users u ON u.id = us.user_id
");
$series = $stmt->fetchAll();
?>
<?php include 'header.php'; ?>
<link rel="stylesheet" href="../CSS/series.css">
<h1>Gestion des Séries</h1>
<?php if (!empty($message)): ?>
    <div class="message-info">
        <?= $message ?>
    </div>
<?php endif; ?>

<?php if ($_SESSION['user_role'] === 'ortho'): ?>
<a href="create_edit_serie.php?mode=new" class="button-add">
    <span class="icon">➕</span> Ajouter une nouvelle série
</a>
<?php endif; ?>

<hr>

<h2>Liste des Séries</h2>
<table border="1">
    <tr>
        <th>Nom</th>
        <th>Description</th>
        <th>Créée par</th>
        <th>Statut</th>
        <th>Actions</th>
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <th>Validation</th>
        <?php endif; ?>
    </tr>
    <?php foreach ($series as $serie): ?>
        <tr>
            <td><?= htmlspecialchars($serie['nom']) ?></td>
            <td><?= htmlspecialchars($serie['description']) ?></td>
            <td><?= htmlspecialchars($serie['prenom'] . ' ' . $serie['nom_user']) ?></td>
            <td>
                <?php if ($serie['valid'] == 1): ?>
                    <span class="status-validated">✅ Validée</span>
                <?php elseif ($serie['valid'] == 0 && $serie['valid_date'] !== null): ?>
                    <span class="status-rejected">❌ Rejetée</span>
                <?php else: ?>
                    <span class="status-pending">⏳ En attente</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($serie['id_orthophoniste'] == $_SESSION['user_id']): ?>
                    <a href="create_edit_serie.php?mode=edit&id=<?= $serie['id'] ?>" class="action-link edit-btn">✏️ Modifier</a>
                    
                    <?php
                    // Vérifier si la série est liée à un patient
                    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM patient_series WHERE serie_id = ?");
                    $stmtCheck->execute([$serie['id']]);
                    $linkedToPatient = $stmtCheck->fetchColumn();

                    if ($linkedToPatient == 0): ?>
                        <form action="delete_serie.php" method="post" style="display:inline;" onsubmit="return confirm('Supprimer définitivement cette série ?')">
                            <input type="hidden" name="serie_id" value="<?= $serie['id'] ?>">
                            <button type="submit" class="danger-btn">🗑 Supprimer</button>
                        </form>
                    <?php endif; ?>

                <?php else: ?>
                    <a href="create_edit_serie.php?mode=view&id=<?= $serie['id'] ?>">Voir</a>
                <?php endif; ?>
            </td>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <td>
                    <?php if ($serie['valid'] == 1): ?>
                        <span class="already-validated">Déjà validée</span>
                    <?php elseif ($serie['valid'] == 0 && $serie['valid_date'] !== null): ?>
                        <span class="already-rejected">Déjà rejetée</span>
                    <?php else: ?>
                        <a href="validate_serie.php?id=<?= $serie['id'] ?>&action=validate" class="button-validate">
                            ✅ Valider
                        </a>
                        <a href="validate_serie.php?id=<?= $serie['id'] ?>&action=reject" class="button-reject">
                            ❌ Rejeter
                        </a>
                    <?php endif; ?>
                </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
</table>