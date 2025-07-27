<?php
require 'auth.php';
require 'db.php';
require_once 'auto_translate.php';

$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_SESSION['user_id'] ?? null;
        $nom = $_POST['nom'] ?? null;
        $description = $_POST['description'] ?? null;
        $serie_id = $_POST['serie_id'] ?? null;

        if (isset($_POST['import_serie'])) {
            $stmt = $pdo->prepare("SELECT * FROM animations WHERE serie_id = ?");
            $stmt->execute([$serie_id]);
            $animations = $stmt->fetchAll();

            $stmt = $pdo->prepare("SELECT image_path FROM series WHERE id = ?");
            $stmt->execute([$serie_id]);
            $image_data = $stmt->fetch();
            $image_path = $image_data['image_path'] ?? null;

            $stmt = $pdo->prepare("INSERT INTO series (nom, description, image_path) VALUES (?, ?, ?)");
            $stmt->execute([$nom, $description, $image_path]);
            $serie_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO animations (serie_id, image_real, image_cartoon, son_path) VALUES (?, ?, ?, ?)");
            foreach ($animations as $anim) {
                $stmt->execute([
                    $serie_id,
                    $anim['image_real'],
                    $anim['image_cartoon'],
                    $anim['son_path']
                ]);
            }

            $stmt = $pdo->prepare("INSERT INTO users_series (user_id, serie_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $serie_id]);
        } else {
            if (!$nom) {
                throw new Exception("Le nom de la série est obligatoire.");
            }

            $image_path = null;
            if (isset($_FILES['image_serie']) && $_FILES['image_serie']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/gif', 'image/png', 'image/jpeg'];
                if (!in_array($_FILES['image_serie']['type'], $allowed_types)) {
                    throw new Exception("Seuls les formats GIF, PNG ou JPEG sont autorisés.");
                }
                $imageInfo = getimagesize($_FILES['image_serie']['tmp_name']);
                if ($imageInfo === false) {
                    throw new Exception("L'image principale n'est pas valide.");
                }
                $filename = uniqid("serie_") . "_" . basename($_FILES['image_serie']['name']);
                $image_path = "uploads/images/" . $filename;
                move_uploaded_file($_FILES['image_serie']['tmp_name'], $image_path);
            }

            if (!$serie_id) {
                $stmt = $pdo->prepare("INSERT INTO series (nom, description, image_path) VALUES (?, ?, ?)");
                $stmt->execute([$nom, $description, $image_path]);
                $serie_id = $pdo->lastInsertId();

                $stmt = $pdo->prepare("INSERT INTO users_series (user_id, serie_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $serie_id]);
            } else {
                if ($image_path) {
                    $stmt = $pdo->prepare("UPDATE series SET nom = ?, description = ?, image_path = ?, valid = 0, valid_date = NULL WHERE id = ?");
                    $stmt->execute([$nom, $description, $image_path, $serie_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE series SET nom = ?, description = ?, valid = 0, valid_date = NULL WHERE id = ?");
                    $stmt->execute([$nom, $description, $serie_id]);
                }
            }

            if (isset($_FILES['images_cartoon'], $_FILES['images_real'], $_FILES['sons'])) {
                $cartoons = $_FILES['images_cartoon'];
                $reals = $_FILES['images_real'];
                $sons = $_FILES['sons'];

                foreach ($cartoons['tmp_name'] as $index => $tmpCartoon) {
                    $tmpReal = $reals['tmp_name'][$index] ?? null;
                    $tmpSon = $sons['tmp_name'][$index] ?? null;

                    if ($tmpCartoon && $tmpReal && $tmpSon) {
                        $cartoonPath = 'uploads/images/' . basename($cartoons['name'][$index]);
                        move_uploaded_file($tmpCartoon, $cartoonPath);

                        $realPath = 'uploads/images/' . basename($reals['name'][$index]);
                        move_uploaded_file($tmpReal, $realPath);

                        $sonPath = 'uploads/sounds/' . basename($sons['name'][$index]);
                        move_uploaded_file($tmpSon, $sonPath);

                        $stmt = $pdo->prepare("INSERT INTO animations (serie_id, image_cartoon, image_real, son_path) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$serie_id, $cartoonPath, $realPath, $sonPath]);
                    }
                }
            }
        }

        header("Location: create_edit_serie.php?id=$serie_id&lang=$lang&success=1");
        exit;
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

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
<?php if (isset($_GET['success'])): ?>
    <div class="message-success">✅ Série enregistrée avec succès.</div>
<?php elseif (!empty($error_message)): ?>
    <div class="message-error">❗ <?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

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

<form method="post" enctype="multipart/form-data">
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

    const addButton = document.querySelector('.add-btn');
    container.parentElement.appendChild(addButton);
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
