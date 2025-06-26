<?php
require 'auth_admin.php';
?>
<?php include 'header.php'; ?>
<link rel="stylesheet" href="../CSS/admin_users.css">
<h1>Gestion des Utilisateurs</h1>

<div style="margin-bottom: 20px;">
    <button onclick="loadUsers('orthophonistes')">👨‍⚕️ Orthophonistes</button>
    <button onclick="loadUsers('patients')">🧍 Patients</button>
</div>

<div id="user-table">Chargement...</div>

<script>
function loadUsers(type) {
    fetch('load_users.php?type=' + type)
        .then(response => response.text())
        .then(html => {
            document.getElementById('user-table').innerHTML = html;
        });
}

// Charger les orthophonistes par défaut
loadUsers('orthophonistes');
</script>
