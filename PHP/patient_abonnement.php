<?php
require 'auth.php';
require 'db.php';
require '../vendor/autoload.php';
require_once 'auto_translate.php';
require_once 'config.php';
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$patient_id = $_GET['patient_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$patient_id || !$user_id) {
    header("Location: patients.php");
    exit;
}

// Vérifie que ce patient appartient à l’orthophoniste connecté
$stmt = $pdo->prepare("
    SELECT p.*
    FROM patients p
    JOIN users_patients up ON p.id = up.patient_id
    WHERE p.id = ? AND up.user_id = ?
");
$stmt->execute([$patient_id, $user_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    echo "<p>⛔ ".t("Accès interdit à ce patient.")."</p>";
    exit;
}

// Création d’une demande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'demande_abonnement') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $type = $_POST['type'];

    $stmt = $pdo->prepare("INSERT INTO subscription_requests (patient_id, user_id, nom, email, type) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$patient_id, $user_id, $nom, $email, $type]);
    $message = "📬 ".t("Demande d’abonnement envoyée à l’administrateur.");
}

// Vérifie l’état actuel de l’abonnement
$stmt = $pdo->prepare("
    SELECT * FROM subscription_requests
    WHERE patient_id = ? AND user_id = ?
    ORDER BY created_at DESC LIMIT 1
");
$stmt->execute([$patient_id, $user_id]);
$abonnement = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= t("Abonnement") ?> - <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></title>
</head>
<body>

<?php include 'header.php'; ?>

<h1>📄 <?= t("Gestion de l’abonnement pour") ?> <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></h1>

<?php if (!empty($message)): ?>
    <p><?= $message ?></p>
<?php endif; ?>

<?php if (!$abonnement || $abonnement['statut'] === 'annule'): ?>
    <h2>➕ <?= t("Faire une demande d’abonnement") ?></h2>
    <form method="post">
        <input type="hidden" name="action" value="demande_abonnement">
        <label><?= t("Nom") ?> : <input type="text" name="nom" required value="<?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?>"></label><br>
        <label><?= t("Email") ?> : <input type="email" name="email" required></label><br>
        <label><?= t("Type") ?> :
            <select name="type">
                <option value="mensuel"><?= t("Mensuel") ?></option>
                <option value="annuel"><?= t("Annuel") ?></option>
            </select>
        </label><br>
        <button type="submit">📩 <?= t("Envoyer la demande") ?></button>
    </form>

<?php elseif ($abonnement['statut'] === 'en_attente'): ?>
    <p>⏳ <?= t("Une demande est en attente de validation par l’administrateur.") ?></p>

<?php elseif ($abonnement['statut'] === 'valide'): ?>
    <p>✅ <?= t("Abonnement actif") ?> (<?= htmlspecialchars($abonnement['type']) ?>)</p>
<?php elseif ($abonnement['statut'] === 'refuse'): ?>
    <p>❌ <?= t("Votre dernière demande d’abonnement a été refusée par l’administrateur.") ?></p>
<?php endif; ?>

</body>
</html>
