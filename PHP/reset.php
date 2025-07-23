<?php
require 'db.php';
require_once 'auto_translate.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

// Étape 1 : Vérifie si le token est valide
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    $error = t("Le lien est invalide ou a expiré.");
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Étape 2 : Mise à jour du mot de passe
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($new_password) < 6) {
        $error = t("Le mot de passe doit contenir au moins 6 caractères.");
    } elseif ($new_password !== $confirm_password) {
        $error = t("Les mots de passe ne correspondent pas.");
    } else {
        // Hash et met à jour le mot de passe
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed, $reset['email']]);

        // Supprime le token
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$reset['email']]);

        $success = t("Mot de passe mis à jour avec succès. Vous pouvez maintenant vous connecter.") . ' <a href="/PHP/login.php">' . t("Se connecter") . '</a>';

    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= t("Réinitialisation du mot de passe") ?></title>
    <link rel="icon" type="image/png" href="/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow rounded">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4"><?= t("Réinitialisation du mot de passe") ?></h3>

                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= $error ?>
                            </div>
                        <?php elseif ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <?= $success ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($reset && !$success): ?>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="password" class="form-label"><?= t("Nouveau mot de passe") ?></label>
                                    <input type="password" class="form-control" name="password" id="password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label"><?= t("Confirmez le mot de passe") ?></label>
                                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary"><?= t("Réinitialiser") ?></button>
                                </div>
                            </form>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS (optionnel) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

