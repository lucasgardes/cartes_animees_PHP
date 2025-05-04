<?php
require 'db.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

// Étape 1 : Vérifie si le token est valide
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    $error = "Le lien est invalide ou a expiré.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Étape 2 : Mise à jour du mot de passe
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($new_password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Hash et met à jour le mot de passe
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed, $reset['email']]);

        // Supprime le token
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$reset['email']]);

        $success = "Mot de passe mis à jour avec succès. Vous pouvez maintenant vous connecter.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 50px; }
        input { padding: 10px; width: 300px; margin-bottom: 10px; }
        button { padding: 10px 20px; background-color: #3498db; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #2980b9; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>

<h1>Réinitialisation du mot de passe</h1>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php elseif ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php if ($reset && !$success): ?>
<form method="post">
    <input type="password" name="password" placeholder="Nouveau mot de passe" required><br>
    <input type="password" name="confirm_password" placeholder="Confirmez le mot de passe" required><br>
    <button type="submit">Réinitialiser</button>
</form>
<?php endif; ?>

</body>
</html>
