<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$role = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="../CSS/header.css">
</head>
<body>
<div class="navbar">
    <div class="nav-left">
        <a href="index.php">🏠 Accueil</a>

        <a href="series.php">🎞️ Toutes les séries</a>
        <?php if ($role === 'admin'): ?>
            <a href="admin_dashboard.php">🛠️ Administration</a>
            <a href="admin_users.php">Gérer les utilisateurs</a>
        <?php else: ?>
            <a href="list_patients.php">🧑‍⚕️ Mes patients</a>
        <?php endif; ?>
    </div>

    <div class="nav-right">
        <div class="user-menu">
            <div class="user-icon" id="userIcon">👤</div>
            <div class="dropdown" id="userDropdown">
                <a href="profil.php">✏️ Modifier mon profil</a>
                <a href="logout.php">🚪 Se déconnecter</a>
            </div>
        </div>
    </div>
</div>
<script src="../JS/header.js"></script>
