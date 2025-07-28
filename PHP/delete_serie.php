<?php
require 'auth.php';
require 'db.php';

$serie_id = $_POST['serie_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$serie_id || $_SESSION['user_role'] !== 'ortho') {
    die("❌ Accès refusé.");
}

// Vérifier que l'utilisateur est bien l'auteur de la série
$stmt = $pdo->prepare("
    SELECT u.id as id_orthophoniste
    FROM series s
    LEFT JOIN users_series us ON us.serie_id = s.id
    LEFT JOIN users u ON u.id = us.user_id
    WHERE s.id = ?
");
$stmt->execute([$serie_id]);
$owner_id = $stmt->fetchColumn();

if ($owner_id != $user_id) {
    die("❌ Vous n'avez pas le droit de supprimer cette série.");
}

// Vérifier que la série n'est pas liée à un patient
$stmt = $pdo->prepare("SELECT COUNT(*) FROM patient_series WHERE serie_id = ?");
$stmt->execute([$serie_id]);
$linked_count = $stmt->fetchColumn();

if ($linked_count > 0) {
    die("❌ Impossible de supprimer une série liée à un patient.");
}

// Fonction pour supprimer un fichier si non utilisé ailleurs
function deleteFileIfUnused($pdo, $column, $path) {
    if (!$path) return;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM animations WHERE $column = ? AND serie_id != ?");
    $stmt->execute([$path, $GLOBALS['serie_id']]);
    $count = $stmt->fetchColumn();
    if ($count == 0 && file_exists($path)) {
        unlink($path);
    }
}

// Récupérer toutes les animations de la série
$stmt = $pdo->prepare("SELECT id, image_real, image_cartoon, son_path FROM animations WHERE serie_id = ?");
$stmt->execute([$serie_id]);
$animations = $stmt->fetchAll();

// Supprimer les stats des animations
$animation_ids = array_column($animations, 'id');
if (!empty($animation_ids)) {
    $in_clause = implode(',', array_fill(0, count($animation_ids), '?'));
    $stmt = $pdo->prepare("DELETE FROM animation_patient WHERE animation_id IN ($in_clause)");
    $stmt->execute($animation_ids);
}

// Supprimer les stats de la série
$stmt = $pdo->prepare("DELETE FROM serie_patient WHERE serie_id = ?");
$stmt->execute([$serie_id]);

// Supprimer les fichiers inutilisés
foreach ($animations as $anim) {
    deleteFileIfUnused($pdo, 'image_real', $anim['image_real']);
    deleteFileIfUnused($pdo, 'image_cartoon', $anim['image_cartoon']);
    deleteFileIfUnused($pdo, 'son_path', $anim['son_path']);
}

// Supprimer les animations
$stmt = $pdo->prepare("DELETE FROM animations WHERE serie_id = ?");
$stmt->execute([$serie_id]);

// Supprimer le lien users_series
$stmt = $pdo->prepare("DELETE FROM users_series WHERE serie_id = ?");
$stmt->execute([$serie_id]);

// Supprimer la série
$stmt = $pdo->prepare("DELETE FROM series WHERE id = ?");
$stmt->execute([$serie_id]);

header("Location: series.php?message=serie_supprimee&lang=$lang");
exit();
