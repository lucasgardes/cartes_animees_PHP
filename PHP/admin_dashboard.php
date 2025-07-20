<?php
require_once 'auto_translate.php';
include 'auth_admin.php';
include 'db.php';

// Récupérer les orthophonistes
$orthos = $pdo->query("SELECT * FROM users WHERE role = 'ortho'")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les patients
$patients = $pdo->query("SELECT * FROM patients")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title><?= t("Admin Dashboard") ?></title>
        <link rel="stylesheet" href="../CSS/admin_dashboard.css">
    </head>
    <body>
        <?php include 'header.php'; ?>
        <h1><?= t("Dashboard Administrateur") ?></h1>

        <h2><?= t("Ajouter un Orthophoniste") ?></h2>
        <form action="add_orthophonist.php" method="post" class="admin-dashboard">
            <input type="text" name="prenom" placeholder="<?= t("Prénom") ?>" required>
            <input type="text" name="nom" placeholder="<?= t("Nom") ?>" required>
            <input type="email" name="email" placeholder="<?= t("Email") ?>" required>
            <input type="password" name="mot_de_passe" placeholder="<?= t("Mot de passe") ?>" required>
            <input type="submit" value="<?= t("Ajouter") ?>">
        </form>

    </body>
</html>
