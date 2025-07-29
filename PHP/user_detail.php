<?php
require 'auth_admin.php';
require 'db.php';
require 'header.php';

$id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? null;

if (!$id || !in_array($type, ['patient', 'orthophoniste'])) {
    die("<div class='alert alert-danger'>❌ Paramètres invalides.</div>");
}

$user = null;
$patients = [];
$title = "";

if ($type === 'patient') {
    $stmt = $pdo->prepare("SELECT p.*, u.prenom AS ortho_prenom, u.nom AS ortho_nom
        FROM patients p
        LEFT JOIN users_patients up ON up.patient_id = p.id
        LEFT JOIN users u ON u.id = up.user_id
        WHERE p.id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) die("<div class='alert alert-danger'>Patient introuvable.</div>");

    $title = "Détails du patient";
    $age = $user['date_naissance'] ? calculerAge($user['date_naissance']) : null;
} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'ortho'");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) die("<div class='alert alert-danger'>Orthophoniste introuvable.</div>");

    $title = "Détails de l'orthophoniste";

    // Charger les patients suivis
    $stmt = $pdo->prepare("SELECT p.* FROM users_patients up
        LEFT JOIN patients p ON p.id = up.patient_id
        WHERE up.user_id = ?");
    $stmt->execute([$id]);
    $patients = $stmt->fetchAll();

    function calculerAge($dateNaissance) {
        $dob = new DateTime($dateNaissance);
        $today = new DateTime();
        return $dob->diff($today)->y;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="../CSS/user_detail.css">
    <link rel="icon" type="image/png" href="/logo.png">
</head>
<body>

<div class="container">
    <h1><?= htmlspecialchars($title) ?></h1>

    <div class="card">
        <?php if ($type === 'patient'): ?>
            <p><strong>👤 Nom :</strong> <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></p>
            <p><strong>🎂 Âge :</strong>
                <?= $age !== null ? htmlspecialchars($age) . ' ans' : 'Date de naissance inconnue' ?>
            </p>
            <p><strong>🩺 Orthophoniste référent :</strong> <?= htmlspecialchars($user['ortho_prenom'] . ' ' . $user['ortho_nom']) ?></p>
        <?php else: ?>
            <p><strong>👤 Nom :</strong> <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></p>
            <p><strong>📧 Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($type === 'orthophoniste'): ?>
        <h2>🧒 Patients suivis :</h2>
        <?php if (empty($patients)): ?>
            <p class="text-muted">Aucun patient lié.</p>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($patients as $p): ?>
                    <li class="list-group-item"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

    <p><a class="btn" href="admin_users.php">🔙 Retour à la gestion</a></p>
</div>

</body>
</html>
