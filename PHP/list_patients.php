<?php
require 'auth.php';
require 'db.php';

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
    <title>Mes Patients</title>
    <link rel="stylesheet" href="../CSS/list_patients.css">
</head>
<body>

<?php include 'header.php'; ?>

<h1>👨‍⚕️ Mes Patients</h1>

<?php if (count($patients) === 0): ?>
    <p>Aucun patient associé à votre compte.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Date de naissance</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Séries</th>
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
                        <a class="btn" href="patient_series.php?patient_id=<?= $patient['id'] ?>">Voir les séries</a>
                        <a class="btn" href="patient_stats.php?patient_id=<?= $patient['id'] ?>">Stats</a>
                        <a class="btn" href="patient_abonnement.php?patient_id=<?= $patient['id'] ?>">Abonnement</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
