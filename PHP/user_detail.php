<?php
require 'auth_admin.php';
require 'db.php';

$id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? null;

if (!$id || !in_array($type, ['patient', 'orthophoniste'])) {
    die("❌ Paramètres invalides.");
}

if ($type === 'patient') {
    $stmt = $pdo->prepare("SELECT p.*, u.prenom AS ortho_prenom, u.nom AS ortho_nom
    FROM patients p
    LEFT JOIN users_patients up ON up.patient_id = p.id
    LEFT JOIN users u ON u.id = up.user_id
    WHERE p.id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) die("Patient introuvable.");
    
    $title = "Détails du patient";

    echo "<h1>$title</h1>";
    echo "<p><strong>Nom :</strong> " . htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . "</p>";
    echo "<p><strong>Âge :</strong> " . htmlspecialchars($user['age']) . "</p>";
    echo "<p><strong>Orthophoniste référent :</strong> " . htmlspecialchars($user['ortho_prenom'] . ' ' . $user['ortho_nom']) . "</p>";

} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'ortho'");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) die("Orthophoniste introuvable.");

    $title = "Détails de l'orthophoniste";
    echo "<h1>$title</h1>";
    echo "<p><strong>Nom :</strong> " . htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . "</p>";
    echo "<p><strong>Email :</strong> " . htmlspecialchars($user['email']) . "</p>";

    // Chercher ses patients :
    $stmt = $pdo->prepare("SELECT p.* FROM
    users_patients up
    LEFT JOIN patients p ON p.id = up.patient_id
    WHERE up.user_id = ?");
    $stmt->execute([$id]);
    $patients = $stmt->fetchAll();

    echo "<h2>Patients suivis :</h2>";
    if (count($patients) === 0) {
        echo "<p>Aucun patient lié.</p>";
    } else {
        echo "<ul>";
        foreach ($patients as $p) {
            echo "<li>" . htmlspecialchars($p['prenom'] . ' ' . $p['nom']) . "</li>";
        }
        echo "</ul>";
    }
}

echo "<p><a href='admin_users.php'>🔙 Retour à la gestion</a></p>";
