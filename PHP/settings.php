<?php
require 'auth.php';
require 'db.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("❌ Accès réservé à l'administrateur.");
}

$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_width = intval($_POST['image_width']);
    $image_height = intval($_POST['image_height']);
    $sound_duration = floatval($_POST['sound_duration']);

    $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE `key` = ?");
    $stmt->execute([$image_width, 'image_width']);
    $stmt->execute([$image_height, 'image_height']);
    $stmt->execute([$sound_duration, 'sound_duration']);

    $success = "✅ Paramètres mis à jour avec succès.";
}

$stmt = $pdo->query("SELECT `key`, `value` FROM settings");
$settings = [];
foreach ($stmt->fetchAll() as $row) {
    $settings[$row['key']] = $row['value'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Paramètres globaux</title>
  <link rel="icon" type="image/png" href="/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container mt-5">
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
      <h4 class="mb-0">🔧 Paramètres de validation des médias</h4>
    </div>
    <div class="card-body">
      <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php endif; ?>

      <form method="post">
        <div class="mb-3">
          <label for="image_width" class="form-label">Largeur requise des images (px)</label>
          <input type="number" class="form-control" id="image_width" name="image_width"
                 value="<?= htmlspecialchars($settings['image_width']) ?>" required>
        </div>

        <div class="mb-3">
          <label for="image_height" class="form-label">Hauteur requise des images (px)</label>
          <input type="number" class="form-control" id="image_height" name="image_height"
                 value="<?= htmlspecialchars($settings['image_height']) ?>" required>
        </div>

        <div class="mb-3">
          <label for="sound_duration" class="form-label">Durée exacte des sons (secondes)</label>
          <input type="number" step="0.1" class="form-control" id="sound_duration" name="sound_duration"
                 value="<?= htmlspecialchars($settings['sound_duration']) ?>" required>
        </div>

        <button type="submit" class="btn btn-success">
          💾 Sauvegarder
        </button>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
