<?php
session_start();
require 'db.php';

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
        header('Location: series.php');
        exit;
    // } else {
        // $error = "Email ou mot de passe incorrect.";
    // }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 50px; }
        input { padding: 10px; width: 300px; margin-bottom: 10px; }
        button { padding: 10px 20px; background-color: #3498db; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #2980b9; }
        .error { color: red; }
    </style>
</head>
<body>

<h1>Connexion Orthophoniste</h1>

<?php if ($error): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<form method="post">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Mot de passe" required><br>
    <button type="submit" name="login">Se connecter</button>
    <a href="forgotten_password.php">Mot de passe oublié ?</a>
</form>

</body>
</html>
