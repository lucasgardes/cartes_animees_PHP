<?php
session_start();
require 'db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    // Vérifie si l'email existe dans la base de données
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Enregistre le token en base
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires]);
        // Prépare le lien de réinitialisation
        $reset_link = "http://localhost/PROJETESI2/cartes_animees_PHP/PHP/reset.php?token=$token";

        // Prépare l'email
        $subject = "Réinitialisation de votre mot de passe";
        $message = "Bonjour,\n\nCliquez sur le lien suivant pour réinitialiser votre mot de passe :\n\n$reset_link\n\nCe lien expirera dans 1 heure.";
        $headers = "From: noreply@tonsite.com";

        // Envoie l'email
        mail($email, $subject, $message, $headers);
        $message = "Un email de réinitialisation a été envoyé si cette adresse est enregistrée.";
    } else {
        // Ne précise pas que l'email est inconnu (bonne pratique de sécurité)
        $message = "Un email de réinitialisation a été envoyé si cette adresse est enregistrée.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="../CSS/forgotten_password.css">
</head>
<body>

<h1>Mot de passe oublié</h1>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="post">
    <input type="email" name="email" placeholder="Votre email" required><br>
    <button type="submit">Envoyer</button>
</form>

</body>
</html>
