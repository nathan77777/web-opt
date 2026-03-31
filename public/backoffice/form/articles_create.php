<?php
session_start();

require_once __DIR__ . '/../../../src/auth.php';
require_once __DIR__ . '/../../../src/database.php';
require_once __DIR__ . '/../../../src/articles.php';

require_auth();

$pdo = getConnection();

$article_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$is_edit_mode = $article_id !== null && $article_id !== false;
$article = null;

if ($is_edit_mode) {
    $article = getArticleById($pdo, (int) $article_id);
    if (!$article) {
        $_SESSION['flash_error'] = 'Article introuvable.';
        header('Location: ../articles_list.php');
        exit;
    }
}

// Fetch categories for the select dropdown
$stmt = $pdo->query('SELECT id, libelles FROM categories ORDER BY libelles ASC');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retrieve flash errors/old input from session if any
$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

$form_defaults = [
    'title' => $article['title'] ?? '',
    'slug' => $article['slug'] ?? '',
    'category_id' => $article['category_id'] ?? '',
    'meta_description' => $article['meta_description'] ?? '',
    'content' => $article['content'] ?? '',
    'published_at' => '',
    'is_active' => !empty($article['is_active']),
    'main_image_index' => '0',
];

if (!empty($article['published_at'])) {
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', (string) $article['published_at']);
    if ($dt !== false) {
        $form_defaults['published_at'] = $dt->format('Y-m-d\TH:i');
    }
}

$form_data = array_merge($form_defaults, $old);

$page_title = $is_edit_mode ? 'Modifier un article' : 'Créer un article';
$submit_label = $is_edit_mode ? 'Mettre à jour l\'article' : 'Créer l\'article';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title) ?></title>
</head>

<body>
    <h1><?= htmlspecialchars($page_title) ?></h1>

    <a href="../articles_list.php">&larr; Retour à la liste</a>

    <?php if (!empty($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="articles_create_process.php" method="POST" enctype="multipart/form-data">
        <?php if ($is_edit_mode): ?>
            <input type="hidden" name="article_id" value="<?= (int) $article_id ?>">
        <?php endif; ?>

        <p>
            <label for="title">Titre *</label><br>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars((string) $form_data['title']) ?>"
                required>
        </p>

        <p>
            <label for="slug">Slug *</label><br>
            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars((string) $form_data['slug']) ?>"
                <?= $is_edit_mode ? 'data-touched="1"' : '' ?> required pattern="[a-z0-9\-]+"
                title="Minuscules, chiffres et tirets uniquement">
        </p>

        <p>
            <label for="category_id">Catégorie</label><br>
            <select id="category_id" name="category_id">
                <option value="">-- Aucune --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ((string) $form_data['category_id'] === (string) $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['libelles']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="meta_description">Meta description *</label><br>
            <input type="text" id="meta_description" name="meta_description"
                value="<?= htmlspecialchars((string) $form_data['meta_description']) ?>" maxlength="255" required>
        </p>

        <p>
            <label for="content">Contenu *</label><br>
            <textarea id="content" name="content" rows="10" cols="60"
                required><?= htmlspecialchars((string) $form_data['content']) ?></textarea>
        </p>

        <p>
            <label for="published_at">Date de publication</label><br>
            <input type="datetime-local" id="published_at" name="published_at"
                value="<?= htmlspecialchars((string) $form_data['published_at']) ?>">
            <small>Laisser vide pour ne pas publier immédiatement.</small>
        </p>

        <p>
            <label for="is_active">
                <input type="checkbox" id="is_active" name="is_active" value="1" <?= !empty($form_data['is_active']) ? 'checked' : '' ?>>
                Article actif
            </label>
        </p>

        <p>
            <label for="images">Images (plusieurs fichiers autorisés)</label><br>
            <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
            <small>Formats acceptés : JPG, PNG, GIF, WEBP. Taille max par image : 5 Mo.</small>
        </p>

        <p>
            <strong>Image principale</strong><br>
            <small>Choisissez l'image principale parmi les fichiers sélectionnés.</small>
            <input type="hidden" id="main_image_index" name="main_image_index"
                value="<?= htmlspecialchars((string) $form_data['main_image_index']) ?>">
        <div id="main-image-choices" style="margin-top:8px;"></div>
        </p>

        <p>
            <button type="submit"><?= htmlspecialchars($submit_label) ?></button>
        </p>
    </form>

    <script>
        const imagesInput = document.getElementById('images');
        const mainImageIndexInput = document.getElementById('main_image_index');
        const mainImageChoices = document.getElementById('main-image-choices');

        function renderMainImageChoices() {
            const files = imagesInput.files;

            if (!files || files.length === 0) {
                mainImageChoices.innerHTML = '<em>Aucune image sélectionnée.</em>';
                mainImageIndexInput.value = '0';
                return;
            }

            const selectedIndex = Number.parseInt(mainImageIndexInput.value, 10);
            const safeIndex = Number.isInteger(selectedIndex) && selectedIndex >= 0 && selectedIndex < files.length
                ? selectedIndex
                : 0;

            let html = '';
            for (let i = 0; i < files.length; i++) {
                const checked = i === safeIndex ? 'checked' : '';
                const fileName = files[i].name
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');

                html += `<label style="display:block;margin-bottom:4px;"><input type="radio" name="main_image_choice" value="${i}" ${checked}> ${fileName}</label>`;
            }

            mainImageChoices.innerHTML = html;

            const selected = mainImageChoices.querySelector('input[name="main_image_choice"]:checked');
            mainImageIndexInput.value = selected ? selected.value : '0';
        }

        imagesInput.addEventListener('change', renderMainImageChoices);
        mainImageChoices.addEventListener('change', function (event) {
            if (event.target && event.target.name === 'main_image_choice') {
                mainImageIndexInput.value = event.target.value;
            }
        });

        renderMainImageChoices();

        // Auto-generate slug from title (client-side convenience)
        document.getElementById('title').addEventListener('input', function () {
            const slugField = document.getElementById('slug');
            if (slugField.dataset.touched) return; // Don't overwrite manual edits
            slugField.value = this.value
                .toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // strip accents
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
        });
        document.getElementById('slug').addEventListener('input', function () {
            this.dataset.touched = '1';
        });
    </script>
</body>

</html>