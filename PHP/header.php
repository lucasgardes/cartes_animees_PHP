<?php
require_once 'auto_translate.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$role = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= t('Tableau de bord') ?></title>
    <link rel="stylesheet" href="../CSS/header.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="icon" type="image/png" href="/logo.png">
</head>
<body>
<div class="navbar">
    <div class="nav-left">
        <a href="/index.php?lang=<?= $lang ?>">🏠 <?= t('Accueil') ?></a>
        <a href="/PHP/series.php?lang=<?= $lang ?>">🎞️ <?= t('Toutes les séries') ?></a>

        <?php if ($role === 'admin'): ?>
            <a href="/PHP/admin_dashboard.php?lang=<?= $lang ?>">🛠️ <?= t('Administration') ?></a>
            <a href="/PHP/admin_users.php?lang=<?= $lang ?>"><?= t('Gérer les utilisateurs') ?></a>
            <a href="/PHP/admin_abonnements.php?lang=<?= $lang ?>">📥 <?= t('Gérer les abonnements') ?></a>
            <a href="/PHP/settings.php?lang=<?= $lang ?>">📥 <?= t('Gérer les paramètres') ?></a>
        <?php else: ?>
            <a href="/PHP/list_patients.php?lang=<?= $lang ?>">🧑‍⚕️ <?= t('Mes patients') ?></a>
        <?php endif; ?>
    </div>

    <div class="nav-right">
        <div class="lang-select">
            <form method="get" onchange="this.submit()">
                <select name="lang" class="lang-dropdown">
                    <option value="fr" <?= $lang === 'fr' ? 'selected' : '' ?>>🇫🇷 Français</option>
                    <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>🇬🇧 English</option>
                </select>
            </form>
        </div>

        <div class="user-menu">
            <div class="user-icon" id="userIcon">👤</div>
            <div class="dropdown" id="userDropdown">
                <a href="/PHP/profil.php?lang=<?= $lang ?>">✏️ <?= t('Modifier mon profil') ?></a>
                <a href="/PHP/logout.php?lang=<?= $lang ?>">🚪 <?= t('Se déconnecter') ?></a>
            </div>
        </div>
    </div>
</div>
<script src="../JS/header.js"></script>
