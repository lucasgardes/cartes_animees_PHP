<?php
require 'auth.php';
require 'db.php';

$message = '';

// Récupération des infos actuelles de l'orthophoniste
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Traitement de la modification
if (isset($_POST['update'])) {
    $newEmail = $_POST['email'] ?? '';
    $newPassword = $_POST['password'] ?? '';

    // Si email changé ou mot de passe rempli, on met à jour
    if ($newEmail) {
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$newEmail, $_SESSION['user_id']]);
        $_SESSION['user_email'] = $newEmail; // met à jour la session
        $message = "✅ Email mis à jour.";
    }

    if ($newPassword) {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $_SESSION['user_id']]);
        $message .= " ✅ Mot de passe mis à jour.";
    }

    // Recharge les infos à jour
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background-color: #f9f9f9; }
        .card { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 400px; }
        input { padding: 10px; width: 100%; margin-bottom: 15px; }
        button { padding: 10px 15px; background-color: #3498db; color: #fff; border: none; cursor: pointer; }
        button:hover { background-color: #2980b9; }
        .success { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<h1>👤 Mon Profil</h1>

<div class="card">
    <?php if ($message): ?>
        <p class="success"><?= $message ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Email :</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>

        <label>Nouveau mot de passe :</label><br>
        <input type="password" name="password" placeholder="Laisser vide si inchangé"><br>

        <button type="submit" name="update">💾 Mettre à jour</button>
    </form>
</div>

<br>
<a href="index.php">⬅ Retour à l'accueil</a>

</body>
</html>
