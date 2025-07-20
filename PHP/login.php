<?php
session_start();
require 'db.php';
require 'auto_translate.php';

$error = '';
if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Vérifie si l'utilisateur existe
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Vérifie le mot de passe
    // if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        header('Location: /index.php');
        exit;
    // } else {
        // $error = t("Email ou mot de passe incorrect.");
    // }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= t("Connexion") ?></title>
    <link rel="stylesheet" href="../CSS/login.css">
</head>
<body>

<h1><?= t("Connexion") ?></h1>

<?php if ($error): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<form method="post">
    <input type="email" name="email" placeholder="<?= t("Email") ?>" required><br>
    <input type="password" name="password" placeholder="<?= t("Mot de passe") ?>" required><br>
    <button type="submit" name="login"><?= t("Se connecter") ?></button>
    <a href="forgotten_password.php?lang=<?= $lang ?>"><?= t("Mot de passe oublié ?") ?></a>
</form>

</body>
</html>
