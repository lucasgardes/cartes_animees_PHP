<?php
require 'auth.php';
require 'db.php';
require '../vendor/autoload.php';
require_once 'auto_translate.php';
require_once '../../config.php';
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$patient_id = $_GET['patient_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$patient_id || !$user_id) {
    header("Location: patients.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*
    FROM patients p
    JOIN users_patients up ON p.id = up.patient_id
    WHERE p.id = ? AND up.user_id = ?
");
$stmt->execute([$patient_id, $user_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    echo "<div class='alert alert-danger m-4'>⛔ " . t("Accès interdit à ce patient.") . "</div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'demande_abonnement') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $type = $_POST['type'];

    $stmt = $pdo->prepare("INSERT INTO subscription_requests (patient_id, user_id, nom, email, type) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$patient_id, $user_id, $nom, $email, $type]);
    $_SESSION['abonnement_message'] = "📬 " . t("Demande d’abonnement envoyée à l’administrateur.");
    header("Location: patient_abonnement.php?patient_id=" . urlencode($patient_id));
    exit;
}

$stmt = $pdo->prepare("
    SELECT * FROM subscription_requests
    WHERE patient_id = ? AND user_id = ?
    ORDER BY created_at DESC LIMIT 1
");
$stmt->execute([$patient_id, $user_id]);
$abonnement = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT * FROM subscription_requests
    WHERE patient_id = ? AND user_id = ? AND statut = 'annule'
    ORDER BY created_at DESC
");
$stmt->execute([$patient_id, $user_id]);
$abonnements_annules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= t("Abonnement") ?> - <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="/logo.png">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container mt-5">
    <h1 class="mb-4">📄 <?= t("Gestion de l’abonnement pour") ?> <strong><?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></strong></h1>

    <?php if (isset($_SESSION['abonnement_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['abonnement_message'] ?></div>
        <?php unset($_SESSION['abonnement_message']); ?>
    <?php endif; ?>

    <?php if (!$abonnement || $abonnement['statut'] === 'annule'): ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">➕ <?= t("Faire une demande d’abonnement") ?></h5>
                <form method="post" class="mt-3">
                    <input type="hidden" name="action" value="demande_abonnement">

                    <div class="mb-3">
                        <label class="form-label"><?= t("Nom") ?></label>
                        <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= t("Email") ?></label>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($patient['email']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= t("Type") ?></label>
                        <select name="type" class="form-select">
                            <option value="mensuel"><?= t("Mensuel") ?></option>
                            <option value="annuel"><?= t("Annuel") ?></option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        📩 <?= t("Envoyer la demande") ?>
                    </button>
                </form>
            </div>
        </div>

    <?php elseif ($abonnement['statut'] === 'en_attente'): ?>
        <div class="alert alert-warning mt-3">
            ⏳ <?= t("Une demande est en attente de validation par l’administrateur.") ?>
        </div>

    <?php elseif ($abonnement['statut'] === 'valide'): ?>
        <div class="alert alert-success mt-3">
            ✅ <?= t("Abonnement actif") ?>
        </div>

    <?php elseif ($abonnement['statut'] === 'refuse'): ?>
        <div class="alert alert-danger mt-3">
            ❌ <?= t("Votre dernière demande d’abonnement a été refusée par l’administrateur.") ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($abonnements_annules)): ?>
        <div class="mt-5">
            <h4>🗂️ <?= t("Historique des abonnements annulés") ?></h4>
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped">
                    <thead class="table-secondary">
                        <tr>
                            <th><?= t("Nom") ?></th>
                            <th><?= t("Email") ?></th>
                            <th><?= t("Type") ?></th>
                            <th><?= t("Date de demande") ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($abonnements_annules as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars($a['nom']) ?></td>
                                <td><?= htmlspecialchars($a['email']) ?></td>
                                <td><?= t(ucfirst($a['type'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($a['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
