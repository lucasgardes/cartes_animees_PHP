<?php
require 'db.php';
if (isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM animations WHERE id = ?")->execute([$_GET['id']]);
}
header("Location: create_edit_serie.php?id=" . $_GET['serie']);
exit;
