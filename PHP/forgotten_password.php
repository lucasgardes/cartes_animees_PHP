<?php
session_start();
require 'db.php';
require_once 'auto_translate.php';
require_once '../../config.php'; // contient tes identifiants SMTP
require_once '../vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    // Vérifie si l'email existe
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Enregistre le token
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires]);

        // Lien de réinitialisation
        $reset_link = "https://hotpink-armadillo-416034.hostingersite.com/PHP/reset.php?token=$token";

        // Envoi de l'email avec PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';              // ou autre selon ton hébergeur
            $mail->SMTPAuth = true;
            $mail->Username = MAIL_USER;                 // depuis config.php
            $mail->Password = MAIL_MDP;                  // idem
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom(MAIL_USER, 'Support');
            $mail->addAddress($email);

            $mail->isHTML(false);
            $mail->Subject = t("Réinitialisation de votre mot de passe");
            $mail->Body = t("Bonjour,") . "\n\n" .
                          t("Cliquez sur le lien suivant pour réinitialiser votre mot de passe :") . "\n" .
                          $reset_link . "\n\n" .
                          t("Ce lien expirera dans 1 heure.");

            $mail->send();
        } catch (Exception $e) {
            $message = t("Erreur lors de l'envoi de l'email : ") . $mail->ErrorInfo;
        }
    }

    // Toujours renvoyer le même message pour éviter les fuites
    if (!$message) {
        $message = t("Un email de réinitialisation a été envoyé si cette adresse est enregistrée.");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="/logo.png">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow rounded">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4"><?= t("Réinitialisation du mot de passe") ?></h3>

                        <?php if (!empty($message)): ?>
                            <div class="alert alert-info" role="alert">
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label"><?= t("Adresse e-mail") ?></label>
                                <input type="email" class="form-control" name="email" id="email" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary"><?= t("Envoyer le lien de réinitialisation") ?></button>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS (optionnel, pour interactions) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

