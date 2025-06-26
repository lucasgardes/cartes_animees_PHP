<?php
// add_orthophonist.php
include 'auth_admin.php';
include 'db.php';

$firstname = $_POST['firstname'];
$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (firstname, `name`, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'ortho')";
$stmt = $conn->prepare($sql);
$stmt->execute([$firstname, $name, $email, $password]);

header("Location: admin_dashboard.php");
exit();