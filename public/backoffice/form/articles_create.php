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
    <style>
        /* ============================================
           BASE
           ============================================ */
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&display=swap');

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', Arial, sans-serif;
            margin: 0;
            background: #f6f7f8;
            color: #1f2937;
        }

        /* ============================================
           LAYOUT
           ============================================ */
        .wrapper {
            max-width: 760px;
            margin: 0 auto;
            padding: 24px 16px 48px;
        }

        /* ============================================
           LIEN RETOUR
           ============================================ */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #0f766e;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 20px;
            transition: color 0.15s ease;
        }

        .back-link:hover {
            color: #0d6b63;
            text-decoration: underline;
        }

        /* ============================================
           TITRE
           ============================================ */
        h1 {
            margin: 0 0 20px;
            font-size: 1.6rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        /* ============================================
           ERREURS
           ============================================ */
        .errors {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 22px;
            list-style: none;
            padding-left: 16px;
        }

        .errors li {
            color: #991b1b;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 3px 0;
        }

        .errors li::before {
            content: '⚠ ';
        }

        /* ============================================
           PANEL FORMULAIRE
           ============================================ */
        .panel {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(15, 23, 42, 0.07);
            padding: 32px;
        }

        /* ============================================
           CHAMPS
           ============================================ */
        .field {
            margin-bottom: 22px;
        }

        .field:last-child {
            margin-bottom: 0;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
            font-size: 0.875rem;
            color: #374151;
        }

        label .required {
            color: #ef4444;
            margin-left: 2px;
        }

        input[type="text"],
        input[type="datetime-local"],
        select,
        textarea {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 0.92rem;
            font-family: inherit;
            color: #1f2937;
            background: #f9fafb;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
            appearance: none;
        }

        input[type="text"]:focus,
        input[type="datetime-local"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #0f766e;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.14);
            background: #ffffff;
        }

        textarea {
            resize: vertical;
            min-height: 200px;
            line-height: 1.65;
        }

        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%236b7280' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
        }

        small {
            display: block;
            margin-top: 5px;
            color: #9ca3af;
            font-size: 0.8rem;
        }

        /* ============================================
           SÉPARATEUR DE SECTION
           ============================================ */
        .section-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #9ca3af;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 8px;
            margin: 28px 0 20px;
        }

        /* ============================================
           CHECKBOX
           ============================================ */
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.92rem;
            color: #1f2937;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            border: 2px solid #d1d5db;
            border-radius: 5px;
            accent-color: #0f766e;
            cursor: pointer;
            flex-shrink: 0;
        }

        /* ============================================
           INPUT FILE
           ============================================ */
        input[type="file"] {
            width: 100%;
            border: 2px dashed #d1d5db;
            border-radius: 10px;
            padding: 14px;
            font-size: 0.875rem;
            font-family: inherit;
            color: #6b7280;
            background: #f9fafb;
            cursor: pointer;
            transition: border-color 0.18s ease, background 0.18s ease;
        }

        input[type="file"]:hover {
            border-color: #0f766e;
            background: #f0fdf9;
        }

        /* ============================================
           APERÇU IMAGE PRINCIPALE
           ============================================ */
        #main-image-choices {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        /* ============================================
           BOUTON SUBMIT
           ============================================ */
        .btn-submit {
            display: inline-block;
            margin-top: 8px;
            padding: 13px 28px;
            border: none;
            border-radius: 10px;
            background: #0f766e;
            color: #ffffff;
            font-family: inherit;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.18s ease, box-shadow 0.18s ease, transform 0.1s ease;
            letter-spacing: 0.01em;
            min-height: 48px;
        }

        .btn-submit:hover {
            background: #0d6b63;
            box-shadow: 0 4px 14px rgba(15, 118, 110, 0.28);
        }

        .btn-submit:active {
            transform: scale(0.99);
            box-shadow: none;
        }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 640px) {
            .panel {
                padding: 20px 16px;
                border-radius: 12px;
            }

            h1 {
                font-size: 1.3rem;
            }

            .btn-submit {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <h1><?= htmlspecialchars($page_title) ?></h1>

    <a class="back-link" href="../articles_list.php">&larr; Retour à la liste</a>

    <?php if (!empty($errors)): ?>
        <ul class="errors">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<div>
    <form action="articles_create_process.php" method="POST" enctype="multipart/form-data">
        <?php if ($is_edit_mode): ?>
            <input type="hidden" name="article_id" value="<?= (int) $article_id ?>">
        <?php endif; ?>

        <div class="field">
            <label for="title">Titre *</label><br>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars((string) $form_data['title']) ?>"
                required>
        </div>

        <div class="field">
            <label for="slug">Slug *</label><br>
            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars((string) $form_data['slug']) ?>"
                <?= $is_edit_mode ? 'data-touched="1"' : '' ?> required pattern="[a-z0-9\-]+"
                title="Minuscules, chiffres et tirets uniquement">
        </div>

        <div class="field">
            <label for="category_id">Catégorie</label><br>
            <select id="category_id" name="category_id">
                <option value="">-- Aucune --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ((string) $form_data['category_id'] === (string) $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['libelles']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label for="meta_description">Meta description *</label><br>
            <input type="text" id="meta_description" name="meta_description"
                value="<?= htmlspecialchars((string) $form_data['meta_description']) ?>" maxlength="255" required>
        </div>

        <div class="field">
            <label for="content">Contenu *</label><br>
            <textarea id="content" name="content" rows="10" cols="60"
                required><?= htmlspecialchars((string) $form_data['content']) ?></textarea>
        </div>

        <div class="field">
            <label for="published_at">Date de publication</label><br>
            <input type="datetime-local" id="published_at" name="published_at"
                value="<?= htmlspecialchars((string) $form_data['published_at']) ?>">
            <small>Laisser vide pour ne pas publier immédiatement.</small>
        </div>

        <div class="field">
            <label class="checkbox-label" for="is_active">
                <input type="checkbox" id="is_active" name="is_active" value="1" <?= !empty($form_data['is_active']) ? 'checked' : '' ?>>
                Article actif
            </label>
        </div>

        <div class="field">
            <label for="images">Images (plusieurs fichiers autorisés)</label><br>
            <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
            <small>Formats acceptés : JPG, PNG, GIF, WEBP. Taille max par image : 5 Mo.</small>
        </div>

        <div class="field">
            <strong>Image principale</strong><br>
            <small>Choisissez l'image principale parmi les fichiers sélectionnés.</small>
            <input type="hidden" id="main_image_index" name="main_image_index"
                value="<?= htmlspecialchars((string) $form_data['main_image_index']) ?>">
        <div id="main-image-choices" style="margin-top:8px;"></div>
        </div>

        <div class="field">
            <button type="submit" class="btn-submit">
        </div>
    </form>
</div>
    <script src="../../assets/js/article_create.js"></script>
</body>

</html>