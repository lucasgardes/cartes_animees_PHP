<?php
require 'auth_admin.php';
require 'db.php';
require_once 'auto_translate.php';

$type = $_GET['type'] ?? 'orthophonistes';

if ($type === 'patients') {
    $stmt = $pdo->query("SELECT p.*, u.prenom AS ortho_prenom, u.nom AS ortho_nom
    FROM patients p
    LEFT JOIN users_patients up ON up.patient_id = p.id
    LEFT JOIN users u ON u.id = up.user_id");
    $list = $stmt->fetchAll();

    echo "<h2>Liste des Patients</h2>
          <table>
          <thead>
            <tr>
              <th>Nom</th>
              <th>Orthophoniste</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>";

    foreach ($list as $p) {
        echo "<tr>
                <td data-label='Nom'>
                    <a href='user_detail.php?id=" . $p['id'] . "&type=patient&lang=<?= $lang ?>'>" .
                    htmlspecialchars($p['prenom'] . ' ' . $p['nom']) .
                    "</a>
                </td>
                <td data-label='Orthophoniste'>" . htmlspecialchars($p['ortho_prenom'] . ' ' . $p['ortho_nom']) . "</td>
                <td data-label='Actions'>
                    <form method='post' action='delete_patient.php' onsubmit='return confirm(\"Supprimer ce patient ?\")'>
                        <input type='hidden' name='patient_id' value='" . $p['id'] . "'>
                        <button type='submit'>🗑 Supprimer</button>
                    </form>
                </td>
              </tr>";
    }

    echo "</tbody></table>";
} else {
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'ortho'");
    $list = $stmt->fetchAll();

    echo "<h2>Liste des Orthophonistes</h2>
          <table>
          <thead>
            <tr>
              <th>Nom</th>
              <th>Email</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>";

    foreach ($list as $u) {
        echo "<tr>
                <td data-label='Nom'>
                    <a href='user_detail.php?id=" . $u['id'] . "&type=orthophoniste&lang=<?= $lang ?>'>" .
                    htmlspecialchars($u['prenom'] . ' ' . $u['nom']) .
                    "</a>
                </td>
                <td data-label='Email'>" . htmlspecialchars($u['email']) . "</td>
                <td data-label='Actions'>
                    <form method='post' action='delete_orthophonist.php' onsubmit='return confirm(\"Supprimer cet orthophoniste ?\")'>
                        <input type='hidden' name='user_id' value='" . $u['id'] . "'>
                        <button type='submit'>🗑 Supprimer</button>
                    </form>
                </td>
              </tr>";
    }

    echo "</tbody></table>";
}
