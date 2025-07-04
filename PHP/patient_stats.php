<?php
require 'auth.php';
require 'db.php';
require_once 'auto_translate.php';

$patient_id = $_GET['patient_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$view = $_GET['view'] ?? 'temps';

if (!$patient_id || !$user_id) {
    header("Location: patients.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*
    FROM patients p
    JOIN users_patients up ON p.id = up.patient_id
    WHERE p.id = ? AND up.user_id = ?
");
$stmt->execute([$patient_id, $user_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    echo "<p>" . t("⚠️ Accès non autorisé à ce patient.") . "</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= t("Statistiques") ?> - <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></title>
    <link rel="stylesheet" href="../CSS/patient_stats.css">
</head>
<body>

<?php include 'header.php'; ?>

<h1>📊 <?= t("Statistiques de") ?> <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></h1>

<div class="nav">
    <a href="?patient_id=<?= $patient_id ?>&view=temps&lang=<?= $lang ?>" class="<?= $view === 'temps' ? 'active' : '' ?>">⏱️ <?= t("Temps passé par jour") ?></a>
    <a href="?patient_id=<?= $patient_id ?>&view=relectures&lang=<?= $lang ?>" class="<?= $view === 'relectures' ? 'active' : '' ?>">🔁 <?= t("Relectures de sons") ?></a>
</div>

<?php if ($view === 'temps'): ?>
    <?php
    $start = $_GET['start'] ?? null;
    $end = $_GET['end'] ?? null;

    $conditions = "sp.patient_id = ?";
    $params = [$patient_id];

    if ($start && $end) {
        $conditions .= " AND sp.start_time BETWEEN ? AND ?";
        $params[] = $start . " 00:00:00";
        $params[] = $end . " 23:59:59";
    }

    $stmt = $pdo->prepare("
        SELECT DATE(sp.start_time) AS jour,
               COUNT(*) AS nb_sessions,
               SUM(TIMESTAMPDIFF(SECOND, sp.start_time, sp.end_time)) AS temps_total
        FROM serie_patient sp
        WHERE $conditions
        GROUP BY jour
        ORDER BY jour DESC
    ");
    $stmt->execute($params);
    $jours = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <h2>⏱️ <?= t("Temps passé par jour") ?></h2>

    <form method="get" style="margin-bottom: 20px;">
        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">
        <input type="hidden" name="view" value="temps">
        <label><?= t("Date début") ?> :
            <input type="date" name="start" value="<?= htmlspecialchars($start) ?>">
        </label>
        <label><?= t("Date fin") ?> :
            <input type="date" name="end" value="<?= htmlspecialchars($end) ?>">
        </label>
        <button type="submit"><?= t("Filtrer") ?></button>
    </form>

    <?php if (empty($jours)): ?>
        <p><?= t("Aucune session trouvée pour cette période.") ?></p>
    <?php else: ?>
        <table>
            <tr>
                <th>📅 <?= t("Jour") ?></th>
                <th>🧭 <?= t("Nombre de sessions") ?></th>
                <th>⏳ <?= t("Temps total") ?></th>
            </tr>
            <?php foreach ($jours as $jour): ?>
                <tr>
                    <td><?= htmlspecialchars($jour['jour']) ?></td>
                    <td><?= (int)$jour['nb_sessions'] ?></td>
                    <td><?= gmdate("H:i:s", $jour['temps_total']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

<?php elseif ($view === 'relectures'): ?>
    <?php
    $start = $_GET['start'] ?? null;
    $end = $_GET['end'] ?? null;
    $serieId = $_GET['serie_id'] ?? null;

    $where = "ap.patient_id = ?";
    $params = [$patient_id];

    if ($start && $end) {
        $where .= " AND ap.created_at BETWEEN ? AND ?";
        $params[] = $start . " 00:00:00";
        $params[] = $end . " 23:59:59";
    }

    $stmt = $pdo->prepare("
        SELECT DISTINCT s.id, s.nom
        FROM animation_patient ap
        JOIN animations a ON a.id = ap.animation_id
        JOIN series s ON s.id = a.serie_id
        WHERE $where
        ORDER BY s.nom
    ");
    $stmt->execute($params);
    $seriesList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <h2>🔁 <?= t("Relectures de sons") ?></h2>

    <form method="get" style="margin-bottom: 20px;">
        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">
        <input type="hidden" name="view" value="relectures">
        <label><?= t("Date début") ?> :
            <input type="date" name="start" value="<?= htmlspecialchars($start) ?>">
        </label>
        <label><?= t("Date fin") ?> :
            <input type="date" name="end" value="<?= htmlspecialchars($end) ?>">
        </label>
        <label><?= t("Série") ?> :
            <select name="serie_id">
                <option value="">-- <?= t("Sélectionner") ?> --</option>
                <?php foreach ($seriesList as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $serieId == $s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit"><?= t("Afficher") ?></button>
    </form>

    <?php if ($serieId): ?>
        <?php
        $where .= " AND a.serie_id = ?";
        $params[] = $serieId;

        $stmt = $pdo->prepare("
            SELECT a.image_real, a.image_cartoon, a.son_path, SUM(ap.replay_count) AS total_replays
            FROM animation_patient ap
            JOIN animations a ON a.id = ap.animation_id
            WHERE $where
            GROUP BY a.id
            ORDER BY total_replays DESC
        ");
        $stmt->execute($params);
        $replays = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if (empty($replays)): ?>
            <p><?= t("Aucune relecture pour cette série sur la période sélectionnée.") ?></p>
        <?php else: ?>
            <table>
                <tr>
                    <th><?= t("Image Cartoon") ?></th>
                    <th><?= t("Image Réelle") ?></th>
                    <th><?= t("Son") ?></th>
                    <th><?= t("Nombre de relectures") ?></th>
                </tr>
                <?php foreach ($replays as $row): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($row['image_cartoon']) ?>" alt="Cartoon" width="100"></td>
                        <td><img src="<?= htmlspecialchars($row['image_real']) ?>" alt="Réelle" width="100"></td>
                        <td>
                            <audio controls>
                                <source src="<?= htmlspecialchars($row['son_path']) ?>" type="audio/mpeg">
                                <?= t("Votre navigateur ne supporte pas l’audio HTML5.") ?>
                            </audio>
                        </td>
                        <td><?= (int)$row['total_replays'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>
