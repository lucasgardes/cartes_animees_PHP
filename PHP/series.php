<?php
require 'auth.php';
require 'db.php';

// Récupérer les séries existantes
$stmt = $pdo->prepare("
        SELECT s.* 
        FROM users_series us
        LEFT JOIN series s ON s.id = us.serie_id
        WHERE us.user_id = ?
    ");
$stmt->execute([$_SESSION['user_id']]);
$series = $stmt->fetchAll();
?>
<?php include 'header.php'; ?>
<h1>Gestion des Séries</h1>

<a href="create_edit_serie.php?mode=new">➕ Ajouter une nouvelle série</a>

<hr>

<h2>Liste des Séries</h2>
<table border="1">
    <tr><th>Nom</th><th>Description</th><th>Actions</th></tr>
    <?php foreach ($series as $serie): ?>
        <tr>
            <td><?= htmlspecialchars($serie['nom']) ?></td>
            <td><?= htmlspecialchars($serie['description']) ?></td>
            <td>
                <a href="create_edit_serie.php?mode=edit&id=<?= $serie['id'] ?>">Modifier</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
