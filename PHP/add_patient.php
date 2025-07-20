<?php
require 'auth.php';
require 'db.php';
require_once 'auto_translate.php';
require '../vendor/autoload.php';
require_once '../../config.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SESSION['user_role'] !== 'ortho') {
    header('Location: list_patients.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $date_naissance = $_POST['date_naissance'];
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);

    if (!$nom || !$prenom || !$date_naissance) {
        $errors[] = t("Tous les champs obligatoires ne sont pas remplis.");
    }

    if (empty($errors)) {
        // Insertion dans patients
        $stmt = $pdo->prepare("INSERT INTO patients (nom, prenom, date_naissance, email, telephone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $date_naissance, $email, $telephone]);
        $patient_id = $pdo->lastInsertId();

        // Association automatique
        $stmt2 = $pdo->prepare("INSERT INTO users_patients (user_id, patient_id) VALUES (?, ?)");
        $stmt2->execute([$_SESSION['user_id'], $patient_id]);

        // Si email est fourni, envoi d’un lien de création de mot de passe
        if (!empty($email)) {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', time() + 24 * 60 * 60); // expire dans 24h

            // Enregistrement dans password_resets
            $stmt3 = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt3->execute([$email, $token, $expires_at]);

            // Création du lien
            $reset_link = "https://hotpink-armadillo-416034.hostingersite.com/PHP/create_password.php?token=" . $token;

            $mail = new PHPMailer(true);

            try {
                // Configuration SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_USER;
                $mail->Password = MAIL_MDP;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Expéditeur et destinataire
                $mail->setFrom(MAIL_USER, 'Votre Application');
                $mail->addAddress($email, "$prenom $nom");

                // Contenu
                $mail->isHTML(false);
                $mail->Subject = "Création de votre mot de passe";
                $mail->Body = "Bonjour $prenom $nom,\n\nCliquez sur ce lien pour définir votre mot de passe :\n$reset_link\n\nCe lien expirera dans 24 heures.";

                $mail->send();
            } catch (Exception $e) {
                $errors[] = t("L'email n'a pas pu être envoyé. Erreur: ") . $mail->ErrorInfo;
            }
        }

        if (empty($errors)) {
            header("Location: list_patients.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= t("Ajouter un patient") ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <h1 class="mb-4"><?= t("Ajouter un nouveau patient") ?></h1>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="card p-4 shadow-sm">
                <div class="mb-3">
                    <label for="nom" class="form-label"><?= t("Nom") ?>*</label>
                    <input type="text" name="nom" id="nom" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="prenom" class="form-label"><?= t("Prénom") ?>*</label>
                    <input type="text" name="prenom" id="prenom" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="date_naissance" class="form-label"><?= t("Date de naissance") ?>*</label>
                    <input type="date" name="date_naissance" id="date_naissance" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label"><?= t("Email") ?></label>
                    <input type="email" name="email" id="email" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="telephone" class="form-label"><?= t("Téléphone") ?></label>
                    <input type="text" name="telephone" id="telephone" class="form-control">
                </div>

                <div class="d-flex justify-content-center gap-3 mt-3">
                    <a href="list_patients.php" class="btn btn-secondary"><?= t("Annuler") ?></a>
                    <button type="submit" class="btn btn-primary"><?= t("Créer le patient") ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
