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
    $newFirstname = $_POST['firstname'] ?? '';
    $newLastname = $_POST['lastname'] ?? '';
    $newPassword = $_POST['password'] ?? '';

    if ($newFirstname) {
        $stmt = $pdo->prepare("UPDATE users SET prenom = ? WHERE id = ?");
        $stmt->execute([$newFirstname, $_SESSION['user_id']]);
        $message = "✅ Prénom mis à jour.";
    }
    if ($newLastname) {
        $stmt = $pdo->prepare("UPDATE users SET nom = ? WHERE id = ?");
        $stmt->execute([$newLastname, $_SESSION['user_id']]);
        $message .= "✅ Nom mis à jour.";
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
        <label>Nom :</label><br>
        <input type="text" name="lastname" value="<?= htmlspecialchars($user['nom']) ?>" required><br>

        <label>Prenom :</label><br>
        <input type="text" name="firstname" value="<?= htmlspecialchars($user['prenom']) ?>" required><br>

        <label>Nouveau mot de passe :</label><br>
        <input type="password" name="password" placeholder="Laisser vide si inchangé"><br>

        <button type="submit" name="update">💾 Mettre à jour</button>
    </form>
</div>

<br>
<a href="index.php">⬅ Retour à l'accueil</a>

</body>
</html>
