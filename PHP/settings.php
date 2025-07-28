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

// Récupérer les valeurs actuelles
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
</head>
<body>
<?php include 'header.php'; ?>

<h1>🔧 Paramètres de validation des médias</h1>

<?php if ($success): ?>
  <div class="message-success"><?= $success ?></div>
<?php endif; ?>

<form method="post">
  <label>Largeur requise des images (px) :</label><br>
  <input type="number" name="image_width" value="<?= htmlspecialchars($settings['image_width']) ?>" required><br><br>

  <label>Hauteur requise des images (px) :</label><br>
  <input type="number" name="image_height" value="<?= htmlspecialchars($settings['image_height']) ?>" required><br><br>

  <label>Durée exacte des sons (secondes) :</label><br>
  <input type="number" step="0.1" name="sound_duration" value="<?= htmlspecialchars($settings['sound_duration']) ?>" required><br><br>

  <button type="submit">💾 Sauvegarder</button>
</form>

</body>
</html>
