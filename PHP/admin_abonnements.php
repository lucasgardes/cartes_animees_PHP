<?php
require 'auth.php';
require 'db.php';
require 'vendor/autoload.php';
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Vérifie que l'utilisateur est admin
if ($_SESSION['role'] !== 'admin') {
    echo "⛔ Accès interdit.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $action = $_POST['action'];

    if ($action === 'valider') {
        try {
            // 1. Créer le client
            $customer = \Stripe\Customer::create([
                'email' => $req['email'],
                'name' => $req['nom'],
                'metadata' => [
                    'patient_id' => $req['patient_id'],
                    'user_id' => $req['user_id'],
                    'demande_id' => $req['id']
                ]
            ]);

            // 2. Créer l’abonnement (avec produit Stripe pré-créé)
            $price_id = ($req['type'] === 'annuel') ? 'price_ANN' : 'price_MENS'; // remplace avec tes ID Stripe
            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [[ 'price' => $price_id ]],
                'payment_behavior' => 'default_incomplete', // nécessite validation par lien
                'expand' => ['latest_invoice.payment_intent']
            ]);

            // 3. Sauvegarder dans la base si besoin
            $pdo->prepare("UPDATE subscription_requests SET statut='valide', stripe_subscription_id=?, validated_at=NOW() WHERE id=?")
                ->execute([$subscription->id, $req['id']]);

            echo "<p>✅ Abonnement Stripe créé avec succès !</p>";

        } catch (\Stripe\Exception\ApiErrorException $e) {
            echo "<p>❌ Erreur Stripe : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } elseif ($action === 'refuser') {
        $pdo->prepare("UPDATE subscription_requests SET statut='refuse' WHERE id=?")
            ->execute([$id]);
    }
}

$stmt = $pdo->query("
    SELECT sr.*, p.nom AS patient_nom, p.prenom AS patient_prenom
    FROM subscription_requests sr
    JOIN patients p ON p.id = sr.patient_id
    WHERE sr.statut = 'en_attente'
");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>📥 Demandes d'abonnement en attente</h1>
<table border="1" cellpadding="6">
    <tr>
        <th>Patient</th>
        <th>Nom</th>
        <th>Email</th>
        <th>Type</th>
        <th>Action</th>
    </tr>
    <?php foreach ($requests as $req): ?>
        <tr>
            <td><?= htmlspecialchars($req['patient_prenom'] . ' ' . $req['patient_nom']) ?></td>
            <td><?= htmlspecialchars($req['nom']) ?></td>
            <td><?= htmlspecialchars($req['email']) ?></td>
            <td><?= htmlspecialchars($req['type']) ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $req['id'] ?>">
                    <button name="action" value="valider">✅ Valider</button>
                    <button name="action" value="refuser">❌ Refuser</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
