<?php
require 'auth.php';
require 'db.php';
require '../vendor/autoload.php';
require_once 'auto_translate.php';
require_once '../../config.php';
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Vérifie que l'utilisateur est admin
if ($_SESSION['user_role'] !== 'admin') {
    $_SESSION['message'] = "<div class='alert alert-danger mt-3'>⛔ " . t("Accès interdit.") . "</div>";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $action = $_POST['action'];

    $stmt = $pdo->prepare("SELECT * FROM subscription_requests WHERE id = ?");
    $stmt->execute([$id]);
    $req = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$req) {
        $_SESSION['message'] = "<div class='alert alert-danger mt-3'>⚠️ " . t("Demande non trouvée.") . "</div>";
    } elseif ($action === 'valider') {
        try {
            $customer = \Stripe\Customer::create([
                'email' => $req['email'],
                'name' => $req['nom'],
                'metadata' => [
                    'patient_id' => $req['patient_id'],
                    'user_id' => $req['user_id'],
                    'demande_id' => $req['id']
                ]
            ]);

            $price_id = ($req['type'] === 'annuel') ? STRIPE_PRICE_ID_ANNUEL : STRIPE_PRICE_ID_MENSUEL;

            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [[ 'price' => $price_id ]],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent']
            ]);

            $pdo->prepare("UPDATE subscription_requests SET statut='valide', stripe_subscription_id=?, customer_subscription_id=?, validated_at=NOW() WHERE id=?")
                ->execute([$subscription->id, $customer->id, $req['id']]);

            $_SESSION['message'] = "<div class='alert alert-success mt-3'>" . t("Abonnement Stripe créé avec succès !") . "</div>";
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $_SESSION['message'] = "<div class='alert alert-danger mt-3'>" . t("Erreur Stripe") . " : " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } elseif ($action === 'refuser') {
        $pdo->prepare("UPDATE subscription_requests SET statut='refuse' WHERE id=?")
            ->execute([$id]);
        $_SESSION['message'] = "<div class='alert alert-warning mt-3'>" . t("Demande d’abonnement refusée.") . "</div>";
    }
    // Redirection pour éviter la resoumission du formulaire
    header("Location: abonnement_requests.php");
    exit;
}

$stmt = $pdo->query("
    SELECT sr.*
    FROM subscription_requests sr
    JOIN patients p ON p.id = sr.patient_id
    WHERE sr.statut = 'en_attente'
");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= t("Abonnements") ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="/logo.png">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container mt-4">
    <?php
        if (isset($_SESSION['message'])) {
            echo $_SESSION['message'];
            unset($_SESSION['message']);
        }
    ?>

    <h1 class="mb-4">📥 <?= t("Demandes d'abonnement en attente") ?></h1>

    <?php if (empty($requests)): ?>
        <div class="alert alert-info"><?= t("Aucune demande d’abonnement en attente.") ?></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th><?= t("Patient") ?></th>
                        <th><?= t("Email") ?></th>
                        <th><?= t("Type") ?></th>
                        <th><?= t("Action") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><?= htmlspecialchars($req['nom']) ?></td>
                            <td><?= htmlspecialchars($req['email']) ?></td>
                            <td><span class="badge bg-info text-dark"><?= t($req['type']) ?></span></td>
                            <td>
                                <form method="post" class="d-flex gap-2">
                                    <input type="hidden" name="id" value="<?= $req['id'] ?>">
                                    <button name="action" value="valider" class="btn btn-success btn-sm">✅ <?= t("Valider") ?></button>
                                    <button name="action" value="refuser" class="btn btn-danger btn-sm">❌ <?= t("Refuser") ?></button>
                                </form>
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