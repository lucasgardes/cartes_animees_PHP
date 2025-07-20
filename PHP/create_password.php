<?php
require 'db.php';
require_once 'auto_translate.php';

$token = $_GET['token'] ?? null;
$errors = [];
$success = false;

if (!$token) {
    die("<p>⛔ " . t("Lien invalide.") . "</p>");
}

// Vérification du token
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    die("<p>⛔ " . t("Lien expiré ou invalide.") . "</p>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $errors[] = t("Le mot de passe doit contenir au moins 6 caractères.");
    }

    if ($password !== $confirm) {
        $errors[] = t("Les mots de passe ne correspondent pas.");
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Mise à jour du mot de passe dans patients
        $stmt = $pdo->prepare("UPDATE patients SET password = ? WHERE email = ?");
        $stmt->execute([$hashed, $reset['email']]);

        // Suppression du token
        $pdo->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);

        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= t("Création du mot de passe") ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="/logo.png">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4"><?= t("Créer votre mot de passe") ?></h3>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            ✅ <?= t("Votre mot de passe a été enregistré avec succès.") ?>
                        </div>
                        <a href="login.php" class="btn btn-success"><?= t("Se connecter") ?></a>

                    <?php else: ?>

                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label"><?= t("Nouveau mot de passe") ?></label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><?= t("Confirmer le mot de passe") ?></label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary"><?= t("Valider") ?></button>
                        </form>

                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
