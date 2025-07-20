<?php
require 'auth.php';
require 'db.php';
require_once 'auto_translate.php';

$user_id = $_SESSION['user_id'] ?? null;

// Requête pour récupérer les patients liés à l'utilisateur
$stmt = $pdo->prepare("
    SELECT p.id, p.nom, p.prenom, p.date_naissance, p.email, p.telephone
    FROM patients p
    INNER JOIN users_patients up ON p.id = up.patient_id
    WHERE up.user_id = ?
");
$stmt->execute([$user_id]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= t("Mes Patients") ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="/logo.png">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container mt-5">
    <h1 class="mb-4">👨‍⚕️ <?= t("Mes Patients") ?></h1>

    <?php if ($_SESSION['user_role'] === 'ortho'): ?>
        <div class="mb-3">
            <a href="add_patient.php" class="btn btn-success btn-sm">
                ➕ <?= t("Ajouter un patient") ?>
            </a>
        </div>
    <?php endif; ?>

    <?php if (count($patients) === 0): ?>
        <div class="alert alert-info"><?= t("Aucun patient associé à votre compte.") ?></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th><?= t("Nom") ?></th>
                        <th><?= t("Prénom") ?></th>
                        <th><?= t("Date de naissance") ?></th>
                        <th><?= t("Email") ?></th>
                        <th><?= t("Téléphone") ?></th>
                        <th><?= t("Séries") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><?= htmlspecialchars($patient['nom']) ?></td>
                            <td><?= htmlspecialchars($patient['prenom']) ?></td>
                            <td><?= htmlspecialchars($patient['date_naissance']) ?></td>
                            <td><?= htmlspecialchars($patient['email']) ?></td>
                            <td><?= htmlspecialchars($patient['telephone']) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="patient_series.php?patient_id=<?= $patient['id'] ?>&lang=<?= $lang ?>" class="btn btn-outline-primary"><?= t("📂 Séries") ?></a>
                                    <a href="patient_stats.php?patient_id=<?= $patient['id'] ?>&lang=<?= $lang ?>" class="btn btn-outline-secondary"><?= t("📊 Stats") ?></a>
                                    <a href="patient_abonnement.php?patient_id=<?= $patient['id'] ?>&lang=<?= $lang ?>" class="btn btn-outline-success"><?= t("💳 Abonnement") ?></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
