<?php
require 'auth.php';
require 'db.php';
$user_id = $_SESSION['user_id'] ?? null;

// Récupération des données de base
$nom = $_POST['nom'] ?? null;
$description = $_POST['description'] ?? null;
$serie_id = $_POST['serie_id'] ?? null;

if (isset($_POST['import_serie'])) {
    $stmt = $pdo->prepare("SELECT * FROM animations WHERE serie_id = ?");
    $stmt->execute([$serie_id]);
    $animations = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT image_path FROM series WHERE id = ?");
    $stmt->execute([$serie_id]);
    $image_data = $stmt->fetch();
    $image_path = $image_data['image_path'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO series (nom, description, image_path) VALUES (?, ?, ?)");
    $stmt->execute([$nom, $description, $image_path]);
    $serie_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO animations (serie_id, image_real, image_cartoon, son_path) VALUES (?, ?, ?, ?)");
    foreach ($animations as $anim) {
        $stmt->execute([
            $serie_id,
            $anim['image_real'],
            $anim['image_cartoon'],
            $anim['son_path']
        ]);
    }

    $stmt = $pdo->prepare("INSERT INTO users_series (user_id, serie_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $serie_id]);
    echo "✅ Série importer avec succès.<br>";
} else {
    if (!$nom) {
        die("Le nom de la série est obligatoire.");
    }

    $image_path = null;
    if (isset($_FILES['image_serie']) && $_FILES['image_serie']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/gif', 'image/png', 'image/jpeg'];
        if (!in_array($_FILES['image_serie']['type'], $allowed_types)) {
            die("❗ Seuls les formats GIF, PNG ou JPEG sont autorisés (SVG interdit).");
        }
        $imageInfo = getimagesize($_FILES['image_serie']['tmp_name']);
        if ($imageInfo === false) {
            die("❗ L'image principale n'est pas valide.");
        }
        // if ($imageInfo[0] !== 150 || $imageInfo[1] !== 150) {
        //     die("❗ L'image principale doit faire exactement 150 x 150 pixels.");
        // }

        $filename = uniqid("serie_") . "_" . basename($_FILES['image_serie']['name']);
        $image_path = "uploads/images/" . $filename;
        move_uploaded_file($_FILES['image_serie']['tmp_name'], $image_path);
    }

    // ➕ Création d'une nouvelle série si pas d'ID
    if (!$serie_id) {
        $stmt = $pdo->prepare("INSERT INTO series (nom, description, image_path) VALUES (?, ?)");
        $stmt->execute([$nom, $description, $image_path]);
        $serie_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO users_series (user_id, serie_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $serie_id]);
    } else {
        // 🔄 Sinon, modification de la série
        if ($image_path) {
            $stmt = $pdo->prepare("UPDATE series SET nom = ?, description = ?, image_path = ? WHERE id = ?");
            $stmt->execute([$nom, $description, $image_path, $serie_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE series SET nom = ?, description = ? WHERE id = ?");
            $stmt->execute([$nom, $description, $serie_id]);
        }
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
}

echo "<a href='create_edit_serie.php?id=$serie_id'>🔙 Retour à la série</a> | ";
echo "<a href='series.php'>📃 Retour à la liste des séries</a>";
?>
