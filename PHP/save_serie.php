<?php
require 'auth.php';
require 'db.php';
$user_id = $_SESSION['user_id'] ?? null;

// Récupération des données de base
$nom = $_POST['nom'] ?? null;
$description = $_POST['description'] ?? null;
$serie_id = $_POST['serie_id'] ?? null;

if (!$nom) {
    die("Le nom de la série est obligatoire.");
}

// ➕ Création d'une nouvelle série si pas d'ID
if (!$serie_id) {
    $stmt = $pdo->prepare("INSERT INTO series (nom, description) VALUES (?, ?)");
    $stmt->execute([$nom, $description]);
    $serie_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO users_series (user_id, serie_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $serie_id]);
} else {
    // 🔄 Sinon, modification de la série
    $stmt = $pdo->prepare("UPDATE series SET nom = ?, description = ? WHERE id = ?");
    $stmt->execute([$nom, $description, $serie_id]);
}

// 📥 Traitement des nouveaux uploads (images[] et sons[])
if (isset($_FILES['images']) && isset($_FILES['sons'])) {
    $images = $_FILES['images'];
    $sons = $_FILES['sons'];
    
    foreach ($images['tmp_name'] as $index => $tmpImage) {
        if ($tmpImage && isset($sons['tmp_name'][$index]) && $sons['tmp_name'][$index]) {
            // Stockage image
            $imagePath = 'uploads/images/' . basename($images['name'][$index]);
            move_uploaded_file($tmpImage, $imagePath);

            // Stockage son
            $sonPath = 'uploads/sounds/' . basename($sons['name'][$index]);
            move_uploaded_file($sons['tmp_name'][$index], $sonPath);

            // Insertion en base
            $pdo->prepare("INSERT INTO animations (serie_id, image_path, son_path) VALUES (?, ?, ?)")
                ->execute([$serie_id, $imagePath, $sonPath]);
        }
    }
}

echo "✅ Série enregistrée avec succès.<br>";
echo "<a href='create_edit_serie.php?id=$serie_id'>🔙 Retour à la série</a> | ";
echo "<a href='series.php'>📃 Retour à la liste des séries</a>";
?>
