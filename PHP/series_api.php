<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'cartes_animees';
$username = 'root'; // Modifier selon le serveur
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Ajouter une série avec ses images et sons
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['name']) || !isset($data['images'])) {
        echo json_encode(['error' => 'nom de série et images requis']);
        exit;
    }
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO series (name, description) VALUES (:name, :description)");
        $stmt->execute(['name' => $data['name'], 'description' => $data['description'] ?? '']);
        $serie_id = $pdo->lastInsertId();
        
        $stmtImg = $pdo->prepare("INSERT INTO images (serie_id, image_url, son_url) VALUES (:serie_id, :image_url, :son_url)");
        foreach ($data['images'] as $image) {
            $stmtImg->execute(['serie_id' => $serie_id, 'image_url' => $image['image_url'], 'son_url' => $image['son_url']]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => 'Série et images ajoutées avec succès']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Erreur lors de l\'ajout']);
    }
}

// Modifier une série et ses images
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'], $data['name'], $data['images'])) {
        echo json_encode(['error' => 'ID, name et images requis']);
        exit;
    }
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE series SET name = :name, description = :description WHERE id = :id");
        $stmt->execute(['id' => $data['id'], 'name' => $data['name'], 'description' => $data['description'] ?? '']);

        foreach ($data['images'] as $image) {
            if (isset($image['id']) && $image['id'] > 0) {
                // L'image existe déjà → On met à jour
                $stmtImg = $pdo->prepare("UPDATE images SET image_url = :image_url, son_url = :son_url WHERE id = :id");
                $stmtImg->execute(['id' => $image['id'], 'image_url' => $image['image_url'], 'son_url' => $image['son_url']]);
            } else {
                // Nouvelle image → On insère
                $stmtImg = $pdo->prepare("INSERT INTO images (serie_id, image_url, son_url) VALUES (:serie_id, :image_url, :son_url)");
                $stmtImg->execute(['serie_id' => $data['id'], 'image_url' => $image['image_url'], 'son_url' => $image['son_url']]);
            }
        }
        
        $pdo->commit();
        echo json_encode(['success' => 'Série et images mises à jour']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Erreur lors de la mise à jour']);
    }
}

// Récupérer toutes les séries avec leurs images
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT s.id, s.name, s.description, i.image_url, i.son_url FROM series s LEFT JOIN images i ON s.id = i.serie_id");
    $series = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['id'];
        if (!isset($series[$id])) {
            $series[$id] = ['id' => $id, 'name' => $row['name'], 'description' => $row['description'], 'images' => []];
        }
        if ($row['image_url']) {
            $series[$id]['images'][] = ['image_url' => $row['image_url'], 'son_url' => $row['son_url']];
        }
    }
    
    echo json_encode(array_values($series));
}
?>
