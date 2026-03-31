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

$stmt = $pdo->query('SELECT id, libelles FROM categories ORDER BY libelles ASC');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

$form_defaults = [
    'title'            => $article['title'] ?? '',
    'slug'             => $article['slug'] ?? '',
    'category_id'      => $article['category_id'] ?? '',
    'meta_description' => $article['meta_description'] ?? '',
    'content'          => $article['content'] ?? '',
    'published_at'     => '',
    'is_active'        => !empty($article['is_active']),
    'main_image_index' => '0',
];

if (!empty($article['published_at'])) {
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', (string) $article['published_at']);
    if ($dt !== false) {
        $form_defaults['published_at'] = $dt->format('Y-m-d\TH:i');
    }
}

$form_data    = array_merge($form_defaults, $old);
$page_title   = $is_edit_mode ? 'Modifier un article' : 'Créer un article';
$submit_label = $is_edit_mode ? 'Mettre à jour' : 'Créer l\'article';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($page_title) ?> – Backoffice</title>
    <link rel="stylesheet" href="../../assets/css/backoffice_article_create.css">
</head>

<body>
<div class="wrapper">

    <!-- ── En-tête ── -->
    <div class="page-header">
        <a class="back-link" href="../articles_list.php">&larr; Retour à la liste</a>
        <h1><?= htmlspecialchars($page_title) ?></h1>
    </div>

    <!-- ── Erreurs ── -->
    <?php if (!empty($errors)): ?>
        <ul class="errors">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- ── Formulaire ── -->
    <div class="panel">
        <form action="articles_create_process.php" method="POST" enctype="multipart/form-data">
            <?php if ($is_edit_mode): ?>
                <input type="hidden" name="article_id" value="<?= (int) $article_id ?>">
            <?php endif; ?>

            <!-- Section : Contenu éditorial -->
            <p class="section-title">Contenu éditorial</p>

            <div class="field">
                <label for="title">Titre <span class="required">*</span></label>
                <input type="text" id="title" name="title"
                       value="<?= htmlspecialchars((string) $form_data['title']) ?>"
                       placeholder="Ex : Attaque de missiles sur Téhéran"
                       required>
            </div>

            <div class="field">
                <label for="slug">Slug <span class="required">*</span></label>
                <input type="text" id="slug" name="slug"
                       value="<?= htmlspecialchars((string) $form_data['slug']) ?>"
                    <?= $is_edit_mode ? 'data-touched="1"' : '' ?>
                       required pattern="[a-z0-9\-]+"
                       placeholder="ex : attaque-missiles-teheran"
                       title="Minuscules, chiffres et tirets uniquement">
                <small>Minuscules, chiffres et tirets uniquement. Généré automatiquement depuis le titre.</small>
            </div>

            <div class="field">
                <label for="category_id">Catégorie</label>
                <select id="category_id" name="category_id">
                    <option value="">— Aucune —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= ((string) $form_data['category_id'] === (string) $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['libelles']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="meta_description">Meta description <span class="required">*</span></label>
                <input type="text" id="meta_description" name="meta_description"
                       value="<?= htmlspecialchars((string) $form_data['meta_description']) ?>"
                       maxlength="255" required
                       placeholder="Résumé court pour les moteurs de recherche (max 255 car.)">
                <small>Utilisée pour le SEO et le chapô affiché dans la liste d'articles.</small>
            </div>

            <div class="field">
                <label for="content">Contenu <span class="required">*</span></label>
                <textarea id="content" name="content" required
                          placeholder="Rédigez le corps de l'article ici…"><?= htmlspecialchars((string) $form_data['content']) ?></textarea>
            </div>

            <!-- Section : Publication -->
            <p class="section-title">Publication</p>

            <div class="field">
                <label for="published_at">Date de publication</label>
                <input type="datetime-local" id="published_at" name="published_at"
                       value="<?= htmlspecialchars((string) $form_data['published_at']) ?>">
                <small>Laisser vide pour enregistrer sans publier immédiatement.</small>
            </div>

            <div class="field">
                <label class="checkbox-label" for="is_active">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                        <?= !empty($form_data['is_active']) ? 'checked' : '' ?>>
                    Article actif (visible en frontoffice)
                </label>
            </div>

            <!-- Section : Médias -->
            <p class="section-title">Médias</p>

            <div class="field">
                <label for="images">Images</label>
                <input type="file" id="images" name="images[]"
                       accept="image/jpeg,image/png,image/gif,image/webp" multiple>
                <small>JPG, PNG, GIF ou WEBP — 5 Mo max par fichier. Plusieurs fichiers autorisés.</small>
            </div>

            <div class="field">
                <label>Image principale</label>
                <small>Sélectionnez ci-dessous l'image à afficher en couverture.</small>
                <input type="hidden" id="main_image_index" name="main_image_index"
                       value="<?= htmlspecialchars((string) $form_data['main_image_index']) ?>">
                <div id="main-image-choices"></div>
            </div>

            <!-- Submit -->
            <div class="field">
                <button type="submit" class="btn-submit">
                    <?= htmlspecialchars($submit_label) ?>
                </button>
            </div>

        </form>
    </div><!-- .panel -->

</div><!-- .wrapper -->

<script src="../../assets/js/article_create.js"></script>
</body>

</html>