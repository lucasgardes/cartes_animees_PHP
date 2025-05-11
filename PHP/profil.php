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
    <link rel="stylesheet" href="../CSS/profil.css">
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
