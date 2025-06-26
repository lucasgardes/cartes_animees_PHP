<?php
include 'auth_admin.php';
include 'db.php';

// Récupérer les orthophonistes
$orthos = $pdo->query("SELECT * FROM users WHERE role = 'ortho'")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les patients
$patients = $pdo->query("SELECT * FROM patients")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../CSS/admin_dashboard.css">
</head>
<body>
<?php include 'header.php'; ?>
<h1>Dashboard Administrateur</h1>

<h2>Ajouter un Orthophoniste</h2>
<form action="add_orthophonist.php" method="post">
    <input type="text" name="prenom" placeholder="Prénom" required>
    <input type="text" name="nom" placeholder="Nom" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
    <input type="submit" value="Ajouter">
</form>

<h2>Liste des Orthophonistes</h2>
<ul>
    <?php foreach ($orthos as $ortho): ?>
        <li>
            <?= htmlspecialchars($ortho['prenom'] . ' ' . $ortho['nom']) ?>
            (<?= htmlspecialchars($ortho['email']) ?>)
            <form action="delete_orthophonist.php" method="post" style="display:inline">
                <input type="hidden" name="user_id" value="<?= $ortho['id'] ?>">
                <input type="submit" value="Supprimer">
            </form>
        </li>
    <?php endforeach; ?>
</ul>

<h2>Ajouter un Patient</h2>
<form action="add_patient.php" method="post">
    <input type="text" name="prenom" placeholder="Prénom" required>
    <input type="text" name="nom" placeholder="Nom" required>
    <select name="id_orthophoniste" required>
        <option value="">Sélectionner un orthophoniste</option>
        <?php foreach ($orthos as $ortho): ?>
            <option value="<?= $ortho['id'] ?>">
                <?= htmlspecialchars($ortho['prenom'] . ' ' . $ortho['nom']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="submit" value="Ajouter">
</form>

<h2>Liste des Patients</h2>
<ul>
    <?php foreach ($patients as $patient): ?>
        <li>
            <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?>
            <form action="delete_patient.php" method="post" style="display:inline">
                <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                <input type="submit" value="Supprimer">
            </form>
        </li>
    <?php endforeach; ?>
</ul>

</body>
</html>
