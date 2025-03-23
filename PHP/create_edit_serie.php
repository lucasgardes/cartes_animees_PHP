<?php
require 'auth.php';
require 'db.php';

// Si modification => on récupère la série + ses animations
$serie = null;
$animations = [];
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("
        SELECT s.* 
        FROM series s
        INNER JOIN users_series us ON us.serie_id = s.id
        WHERE us.user_id = ? AND s.id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $_GET['id']]);
    $serie = $stmt->fetch();
    if (!$serie) {
        die("Accès interdit à cette série !");
    }

    $stmt = $pdo->prepare("SELECT * FROM animations WHERE serie_id = ?");
    $stmt->execute([$_GET['id']]);
    $animations = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= $serie ? 'Modifier' : 'Créer' ?> une Série</title>
  <style>
      body { font-family: Arial, sans-serif; padding: 20px; }
      .bloc { border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; background-color: #f9f9f9; }
      button { margin-top: 10px; }
      .remove-btn { background-color: #e74c3c; color: #fff; border: none; padding: 5px 10px; cursor: pointer; }
      .remove-btn:hover { background-color: #c0392b; }
      .add-btn, .submit-btn { background-color: #3498db; color: #fff; border: none; padding: 10px 15px; cursor: pointer; }
      .add-btn:hover, .submit-btn:hover { background-color: #2980b9; }
      label { font-weight: bold; }
  </style>
</head>
<body>

<h1><?= $serie ? 'Modifier' : 'Créer' ?> une Série</h1>

<form method="post" action="save_serie.php" enctype="multipart/form-data">
    <?php if ($serie): ?>
        <input type="hidden" name="serie_id" value="<?= $serie['id'] ?>">
    <?php endif; ?>

    <label>Nom de la série :</label><br>
    <input type="text" name="nom" required style="width: 50%; padding: 5px;" value="<?= $serie['nom'] ?? '' ?>"><br><br>

    <label>Description :</label><br>
    <textarea name="description" rows="4" style="width: 50%; padding: 5px;"><?= $serie['description'] ?? '' ?></textarea><br><br>

    <h2>Images et Sons</h2>
    <div id="media-container">
        <?php foreach ($animations as $anim): ?>
            <div class="bloc">
                <p>GIF actuel : <img src="<?= $anim['image_path'] ?>" width="100"></p>
                <p>Son actuel : <audio controls src="<?= $anim['son_path'] ?>"></audio></p>
                <input type="hidden" name="existing_animations[]" value="<?= $anim['id'] ?>">
                <button type="button" class="remove-btn" onclick="deleteAnimation(<?= $anim['id'] ?>)">🗑 Supprimer cette animation</button>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="add-btn" onclick="addMediaBloc()">➕ Ajouter un nouveau GIF + Son</button><br><br>

    <div id="new-media"></div>

    <button type="submit" class="submit-btn">✅ Enregistrer la Série</button>
    <button type="button" onclick="window.location.href='series.php'">📃 Retour à la liste des séries</button>
</form>

<script>
function addMediaBloc() {
    const container = document.getElementById('new-media');
    const bloc = document.createElement('div');
    bloc.className = 'bloc';

    bloc.innerHTML = `
        <label>GIF :</label><br>
        <input type="file" name="images[]" accept=".gif" required><br><br>
        <label>Son :</label><br>
        <input type="file" name="sons[]" accept="audio/*" required><br><br>
        <button type="button" class="remove-btn" onclick="this.parentElement.remove()">🗑 Supprimer ce bloc</button>
    `;

    container.appendChild(bloc);
}

function deleteAnimation(id) {
    if (confirm("Supprimer définitivement cette animation ?")) {
        window.location.href = "delete_animation.php?id=" + id + "&serie=<?= $serie['id'] ?? '' ?>";
    }
}
</script>

</body>
</html>
