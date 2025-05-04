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
    </style>
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
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
