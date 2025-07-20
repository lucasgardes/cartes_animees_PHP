<?php
require 'PHP/auth.php';
require 'PHP/db.php';
require_once 'PHP/auto_translate.php';
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
    <?php else: ?>
        <div class="card">
            <h2><?= t("Mon Profil") ?></h2>
            <p><strong><?= t("Email") ?> :</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>
    <?php endif; ?>
</body>
</html>
