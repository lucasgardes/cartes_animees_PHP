<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "cartes_animees");
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

// Fonction d'authentification (POST /login)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'login') {
    $data = json_decode(file_get_contents("php://input"), true);
    $username = $data['username'];
    $password = $data['password'];

    $result = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            echo json_encode(["status" => "success", "message" => "Login successful"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid password"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }
}

// Récupérer les séries (GET /series)
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'series') {
    $result = $conn->query("SELECT * FROM series");
    $series = [];
    while ($row = $result->fetch_assoc()) {
        $series[] = $row;
    }
    echo json_encode($series);
}

// Fermer la connexion
$conn->close();
?>
