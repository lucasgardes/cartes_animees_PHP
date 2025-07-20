<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../config.php';

$pdo = new PDO(
    'mysql:host=localhost;dbname=u370019086_cartes_animees;charset=utf8',
    'u370019086_root',
    BDD_MDP
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
