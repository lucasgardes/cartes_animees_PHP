<?php
require_once 'auto_translate.php';
require 'auth.php';
require 'db.php';

$user_id = $_SESSION['user_id'];
$message = '';

// Récupère uniquement **SES** patients
$patients = $pdo->prepare("
    SELECT p.* FROM patients p
    INNER JOIN users_patients up ON up.patient_id = p.id
    WHERE up.user_id = ?
");
$patients->execute([$user_id]);

// Récupère uniquement **SES** séries
$series = $pdo->prepare("
    SELECT s.* FROM series s
    INNER JOIN users_series us ON us.serie_id = s.id
    WHERE us.user_id = ?
");
$series->execute([$user_id]);

// Traitement de l'association
if (isset($_POST['assign'])) {
    $patient_id = $_POST['patient_id'] ?? null;
    $serie_id = $_POST['serie_id'] ?? null;

    if ($patient_id && $serie_id) {
        $check = $pdo->prepare("
            SELECT * FROM users_patients up 
            JOIN users_series us ON us.user_id = up.user_id
            WHERE up.patient_id = ? AND us.serie_id = ? AND up.user_id = ?
        ");
        $check->execute([$patient_id, $serie_id, $user_id]);
        if ($check->fetch()) {
            $pdo->prepare("INSERT IGNORE INTO patient_series (patient_id, serie_id) VALUES (?, ?)")
                ->execute([$patient_id, $serie_id]);
            $message = t("✅ Série bien assignée au patient.");
        } else {
            $message = t("❌ Erreur : Vous n'avez pas accès à ce patient ou cette série.");
        }
    } else {
        $message = t("❌ Veuillez sélectionner un patient et une série.");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= t("Associer une Série à un Patient") ?></title>
    <link rel="stylesheet" href="../CSS/assign_serie_patient.css">
</head>
<body>

<h1>🎯 <?= t("Associer une Série à un Patient") ?></h1>

<?php if ($message): ?>
    <p class="<?= strpos($message, '✅') !== false ? 'success' : 'error' ?>"><?= $message ?></p>
<?php endif; ?>

<form method="post">
    <label><?= t("Choisir un Patient :") ?></label>
    <select name="patient_id" required>
        <option value=""><?= t("-- Sélectionner --") ?></option>
        <?php foreach ($patients as $patient): ?>
            <option value="<?= $patient['id'] ?>"><?= htmlspecialchars($patient['prenom']) ?> <?= htmlspecialchars($patient['nom']) ?></option>
        <?php endforeach; ?>
    </select>

    <label><?= t("Choisir une Série :") ?></label>
    <select name="serie_id" required>
        <option value=""><?= t("-- Sélectionner --") ?></option>
        <?php foreach ($series as $serie): ?>
            <option value="<?= $serie['id'] ?>"><?= htmlspecialchars($serie['nom']) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="assign">✅ <?= t("Associer") ?></button>
</form>

<a href="/index.php?lang=<?= $lang ?>">⬅ <?= t("Retour à l'accueil") ?></a>

</body>
</html>
