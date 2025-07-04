<?php
require 'auth.php';
require 'db.php';
require_once 'auto_translate.php';
// Récupération des infos de l'orthophoniste connecté
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= t("Accueil - Mon Espace") ?></title>
    <link rel="stylesheet" href="../CSS/index.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <h1><?= t("Bienvenue, ") . htmlspecialchars($user['prenom']) . ' ' . htmlspecialchars($user['nom']) ?> 👋</h1>
    <?php if ($user['role'] === 'admin'): ?>
        <div class="card">
            <h2><?= t("Mon Profil") ?></h2>
            <p><strong><?= t("Email") ?> :</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>
        <div class="card">
            <h2><?= t("Navigation") ?></h2>
            <a href="admin_dashboard.php?lang=<?= $lang ?>"><?= t("Gérer les utilisateurs") ?></a>
            <a href="logout.php?lang=<?= $lang ?>" class="logout">🚪 <?= t("Se déconnecter") ?></a>
        </div>
    <?php else: ?>
        <div class="card">
            <h2><?= t("Mon Profil") ?></h2>
            <p><strong><?= t("Email") ?> :</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>

        <div class="card">
            <h2><?= t("Navigation") ?></h2>
            <a href="series.php?lang=<?= $lang ?>">📂 <?= t("Gérer les Séries") ?></a>
            <a href="logout.php?lang=<?= $lang ?>" class="logout">🚪 <?= t("Se déconnecter") ?></a>
        </div>
    <?php endif; ?>
</body>
</html>
