<?php
require 'auth.php';
require 'db.php';

$serie_id = $_POST['serie_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$serie_id || $_SESSION['user_role'] !== 'ortho') {
    die("❌ Accès refusé.");
}

// Vérifier que l'utilisateur est bien l'auteur de la série
$stmt = $pdo->prepare("SELECT id_orthophoniste FROM series WHERE id = ?");
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

// Supprimer les animations associées
$stmt = $pdo->prepare("DELETE FROM animations WHERE serie_id = ?");
$stmt->execute([$serie_id]);

// Supprimer le lien dans users_series si présent
$stmt = $pdo->prepare("DELETE FROM users_series WHERE serie_id = ?");
$stmt->execute([$serie_id]);

// Supprimer la série
$stmt = $pdo->prepare("DELETE FROM series WHERE id = ?");
$stmt->execute([$serie_id]);

header("Location: series.php?message=serie_supprimee");
exit();
