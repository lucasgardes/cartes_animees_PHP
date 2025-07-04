<?php
require 'db.php';
require_once 'auto_translate.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

// Étape 1 : Vérifie si le token est valide
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    $error = t("Le lien est invalide ou a expiré.");
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Étape 2 : Mise à jour du mot de passe
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($new_password) < 6) {
        $error = t("Le mot de passe doit contenir au moins 6 caractères.");
    } elseif ($new_password !== $confirm_password) {
        $error = t("Les mots de passe ne correspondent pas.");
    } else {
        // Hash et met à jour le mot de passe
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed, $reset['email']]);

        // Supprime le token
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$reset['email']]);

        $success = t("Mot de passe mis à jour avec succès. Vous pouvez maintenant vous connecter.");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= t("Réinitialisation du mot de passe") ?></title>
    <link rel="stylesheet" href="../CSS/patient_stats.css">
</head>
<body>

<h1><?= t("Réinitialisation du mot de passe") ?></h1>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php elseif ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php if ($reset && !$success): ?>
<form method="post">
    <input type="password" name="password" placeholder="<?= t("Nouveau mot de passe") ?>" required><br>
    <input type="password" name="confirm_password" placeholder="<?= t("Confirmez le mot de passe") ?>" required><br>
    <button type="submit"><?= t("Réinitialiser") ?></button>
</form>
<?php endif; ?>

</body>
</html>
