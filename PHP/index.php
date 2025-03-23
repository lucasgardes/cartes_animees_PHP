<?php
require 'auth.php';
require 'db.php';


// Récupération des infos de l'orthophoniste connecté
$stmt = $pdo->prepare("SELECT * FROM orthophonistes WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Mon Espace</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background-color: #f9f9f9; }
        h1 { color: #333; }
        .card { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        a { display: inline-block; padding: 10px 15px; background-color: #3498db; color: #fff; text-decoration: none; border-radius: 5px; margin-right: 10px; }
        a:hover { background-color: #2980b9; }
        .logout { background-color: #e74c3c; }
        .logout:hover { background-color: #c0392b; }
    </style>
</head>
<body>

<h1>Bienvenue <?= htmlspecialchars($user['email']) ?> 👋</h1>

<div class="card">
    <h2>Mon Profil</h2>
    <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
</div>

<div class="card">
    <h2>Navigation</h2>
    <a href="series.php">📂 Gérer les Séries</a>
    <a href="logout.php" class="logout">🚪 Se déconnecter</a>
</div>

</body>
</html>
