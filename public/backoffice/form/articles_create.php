<?php
session_start();

require_once __DIR__ . '/../../../src/auth.php';
require_once __DIR__ . '/../../../src/database.php';

require_auth();

$pdo = getConnection();

// Fetch categories for the select dropdown
$stmt = $pdo->query('SELECT id, libelles FROM categories ORDER BY libelles ASC');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retrieve flash errors/old input from session if any
$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Créer un article</title>
</head>

<body>
    <h1>Créer un article</h1>

    <a href="../articles_list.php">&larr; Retour à la liste</a>

    <?php if (!empty($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="articles_create_process.php" method="POST" enctype="multipart/form-data">

        <p>
            <label for="title">Titre *</label><br>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>" required>
        </p>

        <p>
            <label for="slug">Slug *</label><br>
            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($old['slug'] ?? '') ?>" required
                pattern="[a-z0-9\-]+" title="Minuscules, chiffres et tirets uniquement">
        </p>

        <p>
            <label for="category_id">Catégorie</label><br>
            <select id="category_id" name="category_id">
                <option value="">-- Aucune --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (($old['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['libelles']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="meta_description">Meta description *</label><br>
            <input type="text" id="meta_description" name="meta_description"
                value="<?= htmlspecialchars($old['meta_description'] ?? '') ?>" maxlength="255" required>
        </p>

        <p>
            <label for="content">Contenu *</label><br>
            <textarea id="content" name="content" rows="10" cols="60"
                required><?= htmlspecialchars($old['content'] ?? '') ?></textarea>
        </p>

        <p>
            <label for="published_at">Date de publication</label><br>
            <input type="datetime-local" id="published_at" name="published_at"
                value="<?= htmlspecialchars($old['published_at'] ?? '') ?>">
            <small>Laisser vide pour ne pas publier immédiatement.</small>
        </p>

        <p>
            <label>
                <input type="checkbox" name="is_active" value="1" <?= !empty($old['is_active']) ? 'checked' : '' ?>>
                Article actif
            </label>
        </p>

        <p>
            <label for="images">Images (plusieurs fichiers autorisés)</label><br>
            <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
            <small>Formats acceptés : JPG, PNG, GIF, WEBP. Taille max par image : 5 Mo.</small>
        </p>

        <p>
            <button type="submit">Créer l'article</button>
        </p>
    </form>

    <script>
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