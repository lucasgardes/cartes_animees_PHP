<?php
require_once 'auto_translate.php';
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
    <title><?= t("Admin Dashboard") ?></title>
    <link rel="stylesheet" href="../CSS/admin_dashboard.css">
</head>
<body>
<?php include 'header.php'; ?>
<h1><?= t("Dashboard Administrateur") ?></h1>

<h2><?= t("Ajouter un Orthophoniste") ?></h2>
<form action="add_orthophonist.php" method="post">
    <input type="text" name="prenom" placeholder="<?= t("Prénom") ?>" required>
    <input type="text" name="nom" placeholder="<?= t("Nom") ?>" required>
    <input type="email" name="email" placeholder="<?= t("Email") ?>" required>
    <input type="password" name="mot_de_passe" placeholder="<?= t("Mot de passe") ?>" required>
    <input type="submit" value="<?= t("Ajouter") ?>">
</form>

<h2><?= t("Liste des Orthophonistes") ?></h2>
<ul>
    <?php foreach ($orthos as $ortho): ?>
        <li>
            <?= htmlspecialchars($ortho['prenom'] . ' ' . $ortho['nom']) ?>
            (<?= htmlspecialchars($ortho['email']) ?>)
            <form action="delete_orthophonist.php" method="post" style="display:inline">
                <input type="hidden" name="user_id" value="<?= $ortho['id'] ?>">
                <input type="submit" value="<?= t("Supprimer") ?>">
            </form>
        </li>
    <?php endforeach; ?>
</ul>

<h2><?= t("Ajouter un Patient") ?></h2>
<form action="add_patient.php" method="post">
    <input type="text" name="prenom" placeholder="<?= t("Prénom") ?>" required>
    <input type="text" name="nom" placeholder="<?= t("Nom") ?>" required>
    <select name="id_orthophoniste" required>
        <option value=""><?= t("Sélectionner un orthophoniste") ?></option>
        <?php foreach ($orthos as $ortho): ?>
            <option value="<?= $ortho['id'] ?>">
                <?= htmlspecialchars($ortho['prenom'] . ' ' . $ortho['nom']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="submit" value="<?= t("Ajouter") ?>">
</form>

<h2><?= t("Liste des Patients") ?></h2>
<ul>
    <?php foreach ($patients as $patient): ?>
        <li>
            <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?>
            <form action="delete_patient.php" method="post" style="display:inline">
                <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                <input type="submit" value="<?= t("Supprimer") ?>">
            </form>
        </li>
    <?php endforeach; ?>
</ul>

</body>
</html>
