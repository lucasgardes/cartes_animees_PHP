<?php
require 'auth.php';
require 'db.php';

// Récupération des infos de l'orthophoniste connecté
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Mon Espace</title>
    <link rel="stylesheet" href="../CSS/index.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <h1>Bienvenue, <?= htmlspecialchars($user['prenom']) ?> <?= htmlspecialchars($user['nom']) ?> 👋</h1>
    <?php if ($user['role'] === 'admin'): ?>
        <div class="card">
            <h2>Mon Profil</h2>
            <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>
        <div class="card">
            <h2>Navigation</h2>
            <a href="admin_dashboard.php">Gérer les utilisateurs</a>
            <a href="logout.php" class="logout">🚪 Se déconnecter</a>
        </div>
    <?php else: ?>
        <div class="card">
            <h2>Mon Profil</h2>
            <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>

        <div class="card">
            <h2>Navigation</h2>
            <a href="series.php">📂 Gérer les Séries</a>
            <a href="logout.php" class="logout">🚪 Se déconnecter</a>
        </div>
    <?php endif; ?>
</body>
</html>
