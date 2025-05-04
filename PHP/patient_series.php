<?php
require 'auth.php';
require 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
$patient_id = $_GET['patient_id'] ?? null;

if (!$user_id || !$patient_id) {
    die("Accès non autorisé.");
}

// Vérifie que ce patient appartient bien à l'utilisateur
$check = $pdo->prepare("SELECT 1 FROM users_patients WHERE user_id = ? AND patient_id = ?");
$check->execute([$user_id, $patient_id]);
if ($check->rowCount() === 0) {
    die("Ce patient ne vous appartient pas.");
}

// 🔄 Traitement du formulaire d'ajout de série
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serie_id'])) {
    $serie_id = $_POST['serie_id'];

    // Vérifie que la série appartient à l'utilisateur
    $stmt = $pdo->prepare("SELECT 1 FROM users_series WHERE user_id = ? AND serie_id = ?");
    $stmt->execute([$user_id, $serie_id]);
    if ($stmt->rowCount() === 0) {
        die("Vous ne pouvez pas associer une série qui ne vous appartient pas.");
    }

    // Vérifie si la série est déjà associée au patient
    $stmt = $pdo->prepare("SELECT 1 FROM patient_series WHERE patient_id = ? AND serie_id = ?");
    $stmt->execute([$patient_id, $serie_id]);
    if ($stmt->rowCount() === 0) {
        // Ajoute l'association
        $stmt = $pdo->prepare("INSERT INTO patient_series (patient_id, serie_id) VALUES (?, ?)");
        $stmt->execute([$patient_id, $serie_id]);
    }

    // Redirige pour éviter la resoumission du formulaire
    header("Location: patient_series.php?patient_id=" . $patient_id);
    exit;
}

// Infos du patient
$patientInfo = $pdo->prepare("SELECT nom, prenom FROM patients WHERE id = ?");
$patientInfo->execute([$patient_id]);
$patient = $patientInfo->fetch(PDO::FETCH_ASSOC);

// Séries associées
$stmt = $pdo->prepare("
    SELECT s.id, s.nom, s.description
    FROM series s
    INNER JOIN patient_series ps ON s.id = ps.serie_id
    WHERE ps.patient_id = ?
");
$stmt->execute([$patient_id]);
$seriesAssociees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Séries disponibles à associer (celles de l'utilisateur non déjà associées)
$stmt = $pdo->prepare("
    SELECT s.id, s.nom
    FROM series s
    INNER JOIN users_series us ON s.id = us.serie_id
    WHERE us.user_id = ?
");
$stmt->execute([$user_id, $patient_id]);
$seriesDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Séries du patient</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
        }

        th {
            background-color: #f0f0f0;
        }

        h1 {
            margin-top: 20px;
        }

        .btn-back {
            display: inline-block;
            margin-top: 15px;
            padding: 6px 12px;
            background-color: #7f8c8d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-back:hover {
            background-color: #5d6d7e;
        }

        .form-ajout {
            margin-top: 30px;
            padding: 15px;
            background-color: #ecf0f1;
            border-radius: 5px;
        }

        .form-ajout select,
        .form-ajout button {
            padding: 8px;
            margin-right: 10px;
            font-size: 14px;
        }

        .form-ajout button {
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 4px;
        }

        .form-ajout button:hover {
            background-color: #27ae60;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<h1>🎞️ Séries de <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></h1>

<?php if (count($seriesAssociees) === 0): ?>
    <p>Aucune série associée à ce patient.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($seriesAssociees as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['nom']) ?></td>
                    <td><?= htmlspecialchars($s['description']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Formulaire pour ajouter une série -->
<?php if (count($seriesDisponibles) > 0): ?>
    <div class="form-ajout">
        <form method="POST">
            <label for="serie_id">Associer une série :</label>
            <select name="serie_id" id="serie_id" required>
                <?php foreach ($seriesDisponibles as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Ajouter</button>
        </form>
    </div>
<?php endif; ?>

<a class="btn-back" href="patients.php">← Retour aux patients</a>

</body>
</html>
