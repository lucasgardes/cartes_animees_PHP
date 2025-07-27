<?php
require 'auth.php';
require 'db.php';

// Si modification => on récupère la série + ses animations
$serie = null;
$animations = [];
$can_edit = true;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("
        SELECT s.*, u.id as id_orthophoniste
        FROM series s
        LEFT JOIN users_series us ON us.serie_id = s.id
        LEFT JOIN users u ON u.id = us.user_id
        WHERE s.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $serie = $stmt->fetch();
    if (!$serie) {
        die("Accès interdit à cette série !");
    }

    $can_edit = (
        $_SESSION['user_role'] === 'ortho' &&
        $serie['id_orthophoniste'] == $_SESSION['user_id']
    );

    $stmt = $pdo->prepare("SELECT * FROM animations WHERE serie_id = ?");
    $stmt->execute([$_GET['id']]);
    $animations = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= $serie ? 'Modifier' : 'Créer' ?> une Série</title>
  <link rel="stylesheet" href="../CSS/create_edit_serie.css">
  <link rel="icon" type="image/png" href="/logo.png">
</head>
<body>
<?php include 'header.php'; ?>

<?php if ($can_edit): ?>
    <h1><?= $serie ? 'Modifier' : 'Créer' ?> une Série</h1>
    <?php if ($serie && $serie['valid'] == 0 && $serie['valid_date'] !== null): ?>
        <div class="message-rejected">
            ⚠️ Cette série a été rejetée le <?= date('d/m/Y', strtotime($serie['valid_date'])) ?>.
            Vous pouvez la modifier et la sauvegarder pour la resoumette à validation.
        </div>
    <?php elseif ($serie && $serie['valid'] == 0 && $serie['valid_date'] === null): ?>
        <div class="message-pending">
            ⏳ Cette série est en attente de validation.
        </div>
    <?php elseif ($serie && $serie['valid'] == 1): ?>
        <div class="message-validated">
            ✅ Cette série a été validée le <?= date('d/m/Y', strtotime($serie['valid_date'])) ?>.
        </div>
    <?php endif; ?>
<?php endif; ?>

<form method="post" action="save_serie.php" enctype="multipart/form-data">
    <?php if ($serie): ?>
        <input type="hidden" name="serie_id" value="<?= $serie['id'] ?>">
    <?php endif; ?>

    <label>Nom de la série :</label><br>
    <input type="text" name="nom" required style="width: 50%; padding: 5px;" value="<?= $serie['nom'] ?? '' ?>" <?= $can_edit ? '' : 'readonly' ?>><br><br>

    <label>Description :</label><br>
    <textarea name="description" rows="4" style="width: 50%; padding: 5px;" <?= $can_edit ? '' : 'readonly' ?>><?= $serie['description'] ?? '' ?></textarea><br><br>
    
    <label>Image principale de la série :</label><br>
    <?php if (!empty($serie['image_path'])): ?>
        <img src="<?= htmlspecialchars($serie['image_path']) ?>" alt="aperçu" style="max-width: 450px; max-height: 450px;"><br>
    <?php endif; ?>
    <?php if ($can_edit): ?>
        <input type="file" name="image_serie" id="imageSerieInput" accept="image/*"><br><br>
    <?php endif; ?>

    <h2>Images et Sons</h2>
    <div id="media-container">
        <?php foreach ($animations as $anim): ?>
            <div class="bloc">
                <p>GIF cartoon actuel : <img src="<?= $anim['image_cartoon'] ?>" width="100"></p>
                <p>GIF réaliste actuel : <img src="<?= $anim['image_real'] ?>" width="100"></p>
                <p>Son actuel : <audio controls src="<?= $anim['son_path'] ?>"></audio></p>
                <input type="hidden" name="existing_animations[]" value="<?= $anim['id'] ?>">
                <?php if ($can_edit): ?>
                    <button type="button" class="remove-btn" onclick="deleteAnimation(<?= $anim['id'] ?>)">🗑 Supprimer cette animation</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if ($can_edit): ?>
        <button type="button" class="add-btn" onclick="addMediaBloc()">➕ Ajouter un nouveau GIF + Son</button><br><br>
    <?php endif; ?>

    <div id="new-media"></div>
    
    <?php if ($can_edit && $_SESSION['user_role'] === 'ortho'): ?>
        <button type="submit" class="submit-btn">✅ Enregistrer la Série</button>
    <?php elseif ($_SESSION['user_role'] === 'ortho'): ?>
        <input type="hidden" name="import_serie"></input>
        <button type="submit" class="submit-btn">Importer la Série</button>
    <?php endif; ?>
        <button type="button" onclick="window.location.href='series.php?lang=<?= $lang ?>'">📃 Retour à la liste des séries</button>
</form>

<script>
function addMediaBloc() {
    const container = document.getElementById('new-media');
    const bloc = document.createElement('div');
    bloc.className = 'bloc';

    bloc.innerHTML = `
        <label>GIF Cartoon :</label><br>
        <input type="file" name="images_cartoon[]" accept=".gif" class="gif" required><br><br>

        <label>GIF Réaliste :</label><br>
        <input type="file" name="images_real[]" accept=".gif" class="gif" required><br><br>

        <label>Son :</label><br>
        <input type="file" name="sons[]" accept="audio/*" class="sound-check" required><br><br>

        <button type="button" class="remove-btn" onclick="this.parentElement.remove()">🗑 Supprimer ce bloc</button>
    `;

    container.appendChild(bloc);
    bindNewValidationEvents();
}

function deleteAnimation(id) {
    if (confirm("Supprimer définitivement cette animation ?")) {
        window.location.href = "delete_animation.php?id=" + id + "&serie=<?= $serie['id'] ?? '' ?>";
    }
}
function bindNewValidationEvents() {
    const imageInput = document.getElementById('imageSerieInput');
    if (imageInput) {
        imageInput.addEventListener('change', () => {
            const file = imageInput.files[0];
            if (!file) return;

            const img = new Image();
            img.onload = function () {
                if (img.width !== 450 || img.height !== 450) {
                    alert("❗ L'image principale doit faire exactement 450 x 450 pixels.");
                    imageInput.value = "";
                }
            };
            img.onerror = function () {
                alert("❗ Fichier image invalide.");
                imageInput.value = "";
            };
            img.src = URL.createObjectURL(file);
        });
    }
    // GIF cartoon : nom avec crochets
    const gifInputs = document.querySelectorAll('.gif');
    gifInputs.forEach(input => {
        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;

            const img = new Image();
            img.onload = function () {
                if (img.width !== 450 || img.height !== 450) {
                    alert("❗ Le GIF cartoon doit faire exactement 450 x 450 pixels.");
                    input.value = "";
                }
            };
            img.onerror = function () {
                alert("❗ Le fichier sélectionné n'est pas une image valide.");
                input.value = "";
            };
            img.src = URL.createObjectURL(file);
        });
    });

    // Son : durée = 5 secondes
    const soundInputs = document.querySelectorAll('.sound-check');
    soundInputs.forEach(input => {
        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;
            const audio = document.createElement('audio');
            audio.preload = 'metadata';
            audio.onloadedmetadata = function () {
                window.URL.revokeObjectURL(audio.src);
                const duration = audio.duration;
                if (Math.abs(duration - 5) > 0.1) {
                    alert("❗ Le son doit durer exactement 5 secondes.");
                    input.value = "";
                }
            };
            audio.src = URL.createObjectURL(file);
        });
    });
}
window.addEventListener('DOMContentLoaded', bindNewValidationEvents);
</script>

</body>
</html>
