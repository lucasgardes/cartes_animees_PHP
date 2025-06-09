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
if (isset($_FILES['images_cartoon'], $_FILES['images_real'], $_FILES['sons'])) {
    $cartoons = $_FILES['images_cartoon'];
    $reals = $_FILES['images_real'];
    $sons = $_FILES['sons'];
    
    foreach ($cartoons['tmp_name'] as $index => $tmpCartoon) {
        $tmpReal = $reals['tmp_name'][$index] ?? null;
        $tmpSon = $sons['tmp_name'][$index] ?? null;

        if ($tmpCartoon && $tmpReal && $tmpSon) {
            // ➕ Stocker image cartoon
            $cartoonPath = 'uploads/images/' . basename($cartoons['name'][$index]);
            move_uploaded_file($tmpCartoon, $cartoonPath);

            // ➕ Stocker image réaliste
            $realPath = 'uploads/images/' . basename($reals['name'][$index]);
            move_uploaded_file($tmpReal, $realPath);

            // ➕ Stocker son
            $sonPath = 'uploads/sounds/' . basename($sons['name'][$index]);
            move_uploaded_file($tmpSon, $sonPath);

            // ➕ Insérer dans la table animations
            $stmt = $pdo->prepare("
                INSERT INTO animations (serie_id, image_cartoon, image_real, son_path)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$serie_id, $cartoonPath, $realPath, $sonPath]);
        }
    }
}

echo "✅ Série enregistrée avec succès.<br>";
echo "<a href='create_edit_serie.php?id=$serie_id'>🔙 Retour à la série</a> | ";
echo "<a href='series.php'>📃 Retour à la liste des séries</a>";
?>
