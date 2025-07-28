<?php
require 'db.php';

if (!isset($_GET['id']) || !isset($_GET['serie'])) {
    die("ID de l'animation ou de la série manquant.");
}

$animation_id = (int) $_GET['id'];
$serie_id = (int) $_GET['serie'];

// 1. Récupérer les chemins des fichiers
$stmt = $pdo->prepare("SELECT image_real, image_cartoon, son_path FROM animations WHERE id = ?");
$stmt->execute([$animation_id]);
$animation = $stmt->fetch();

if (!$animation) {
    die("Animation introuvable.");
}

$image_real = $animation['image_real'];
$image_cartoon = $animation['image_cartoon'];
$son_path = $animation['son_path'];

// 2. Supprimer les stats associées
$stmt = $pdo->prepare("DELETE FROM animation_patient WHERE animation_id = ?");
$stmt->execute([$animation_id]);

// 3. Supprimer l'animation elle-même
$stmt = $pdo->prepare("DELETE FROM animations WHERE id = ?");
$stmt->execute([$animation_id]);

// 4. Supprimer les fichiers si pas utilisés ailleurs

function deleteFileIfUnused($pdo, $column, $path, $table = 'animations') {
    if (!$path) return;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
    $stmt->execute([$path]);
    $count = $stmt->fetchColumn();
    if ($count == 0 && file_exists($path)) {
        unlink($path);
    }
}

deleteFileIfUnused($pdo, 'image_real', $image_real);
deleteFileIfUnused($pdo, 'image_cartoon', $image_cartoon);
deleteFileIfUnused($pdo, 'son_path', $son_path);

// 5. Redirection
header("Location: create_edit_serie.php?id=$serie_id&lang=$lang&success=1");
exit;
