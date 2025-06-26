<?php
require 'auth_admin.php';
require 'db.php';

if (!isset($_GET['id'])) {
    header("Location: series.php");
    exit();
}

$serie_id = $_GET['id'];
$action = $_GET['action'] ?? 'validate';

// Vérifier si la série existe
$stmt = $pdo->prepare("SELECT valid, valid_date FROM series WHERE id = ?");
$stmt->execute([$serie_id]);
$serie = $stmt->fetch();

if (!$serie) {
    die("Série introuvable.");
}

if ($action === 'validate') {
    // Valider la série
    if ($serie['valid'] == 1) {
        header("Location: series.php?message=deja_validee");
        exit();
    }
    
    $stmt = $pdo->prepare("UPDATE series SET valid = 1, valid_date = NOW() WHERE id = ?");
    $stmt->execute([$serie_id]);
    
    header("Location: series.php?message=validee");
    
} elseif ($action === 'reject') {
    // Rejeter la série (valid = 0 avec une date de rejet)
    if ($serie['valid'] == 0 && $serie['valid_date'] !== null) {
        header("Location: series.php?message=deja_invalidee");
        exit();
    }
    
    $stmt = $pdo->prepare("UPDATE series SET valid = 0, valid_date = NOW() WHERE id = ?");
    $stmt->execute([$serie_id]);
    
    header("Location: series.php?message=invalidee");
    
} else {
    header("Location: series.php?message=action_inconnue");
}

exit();
?>