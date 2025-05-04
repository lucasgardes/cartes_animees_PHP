<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background-color: #2c3e50;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
        }

        .nav-left, .nav-right {
            display: flex;
            align-items: center;
        }

        .user-menu {
            position: relative;
        }

        .user-icon {
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #ecf0f1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2c3e50;
            font-weight: bold;
        }

        .dropdown {
            display: none;
            position: absolute;
            top: 42px;
            right: 0;
            background-color: white;
            color: black;
            min-width: 160px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        .dropdown a {
            color: black;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
        }

        .dropdown a:hover {
            background-color: #f1f1f1;
        }

        .user-menu:hover .dropdown {
            display: block;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="nav-left">
        <a href="index.php">🏠 Accueil</a>
        <a href="series.php">🎞️ Mes séries</a>
        <a href="list_patients.php">🧑‍⚕️ Mes patients</a>
    </div>
    <div class="nav-right">
        <div class="user-menu">
            <div class="user-icon">👤</div>
            <div class="dropdown">
                <a href="profil.php">✏️ Modifier mon profil</a>
                <a href="logout.php">🚪 Se déconnecter</a>
            </div>
        </div>
    </div>
</div>
