<?php
require 'auth.php';
require 'db.php';
require_once 'auto_translate.php';

$user_id = $_SESSION['user_id'] ?? null;
$patient_id = $_GET['patient_id'] ?? null;

if (!$user_id || !$patient_id) {
    die(t("Accès non autorisé."));
}

// Vérifie que ce patient appartient bien à l'utilisateur
$check = $pdo->prepare("SELECT 1 FROM users_patients WHERE user_id = ? AND patient_id = ?");
$check->execute([$user_id, $patient_id]);
if ($check->rowCount() === 0) {
    die(t("Ce patient ne vous appartient pas."));
}

// 🔄 Traitement du formulaire d'ajout de série
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serie_id'])) {
    $serie_id = $_POST['serie_id'];

    // Vérifie que la série appartient à l'utilisateur
    $stmt = $pdo->prepare("SELECT 1 FROM users_series WHERE user_id = ? AND serie_id = ?");
    $stmt->execute([$user_id, $serie_id]);
    if ($stmt->rowCount() === 0) {
        die(t("Vous ne pouvez pas associer une série qui ne vous appartient pas."));
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
    LEFT JOIN patient_series ps ON ps.serie_id = s.id AND ps.patient_id = ?
    WHERE us.user_id = ?
    AND ps.id IS NULL
    AND s.valid = 1
");
$stmt->execute([$user_id, $patient_id]);
$seriesDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
var_dump($user_id);
var_dump($patient_id);
die();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= t("Séries du patient") ?></title>
    <link rel="stylesheet" href="../CSS/patient_series.css">
    <link rel="icon" type="image/png" href="/logo.png">
</head>
<body>

<?php include 'header.php'; ?>

<h1>🎞️ <?= t("Séries de") ?> <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></h1>

<?php if (count($seriesAssociees) === 0): ?>
    <p><?= t("Aucune série associée à ce patient.") ?></p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th><?= t("Nom") ?></th>
                <th><?= t("Description") ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($seriesAssociees as $s): ?>
                <tr>
                    <td>
                        <a href="create_edit_serie.php?mode=edit&id=<?= $s['id'] ?>&lang=<?= $lang ?>">
                            <?= htmlspecialchars($s['nom']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($s['description']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Formulaire pour ajouter une série -->
<div class="form-ajout">
    <?php if (count($seriesDisponibles) > 0): ?>
        <form method="POST">
            <label for="serie_id"><?= t("Associer une série") ?> :</label>
            <select name="serie_id" id="serie_id" required>
                <?php foreach ($seriesDisponibles as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit"><?= t("Ajouter") ?></button>
        </form>
    <?php else: ?>
        <p>✅ <?= t("Toutes vos séries sont déjà associées à ce patient.") ?></p>
    <?php endif; ?>
</div>

<a class="btn-back" href="list_patients.php?lang=<?= $lang ?>">← <?= t("Retour aux patients") ?></a>

</body>
</html>
