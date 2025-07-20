<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /PHP/login.php');
    exit;
}
