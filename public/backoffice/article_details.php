<?php
session_start();

require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/database.php';
require_once __DIR__ . '/../../src/articles.php';

require_auth();

// ── Resolve article id from query string ──────────────────────────────────────

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: articles_list.php');
    exit;
}

$pdo = getConnection();
$article = getArticleById($pdo, $id);

if (!$article) {
    header('Location: articles_list.php');
    exit;
}

$images = getArticleImages($pdo, $id);

// ── Flash message from a previous action ─────────────────────────────────────

$flash = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

// ── Helpers ───────────────────────────────────────────────────────────────────

function fmt_date(?string $dt, string $fallback = '—'): string
{
    if ($dt === null || $dt === '') {
        return $fallback;
    }
    $d = new DateTime($dt);
    return $d->format('d/m/Y à H:i');
}

// Is the image stored locally (starts with /) or hosted externally?
function is_local_image(string $url): bool
{
    return str_starts_with($url, '/');
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Détail article – <?= htmlspecialchars($article['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/article_details.css">
</head>

<body>

    <a href="articles_list.php">&larr; Retour à la liste</a>

    <?php if ($flash): ?>
        <p class="flash"><?= htmlspecialchars($flash) ?></p>
    <?php endif; ?>

    <h1><?= htmlspecialchars($article['title']) ?></h1>

    <p class="meta">
        <?php if ($article['is_active']): ?>
            <span class="badge badge-on">Actif</span>
        <?php else: ?>
            <span class="badge badge-off">Inactif</span>
        <?php endif; ?>
        &nbsp;
        <?php if ($article['published_at']): ?>
            Publié le <?= fmt_date($article['published_at']) ?>
        <?php else: ?>
            <em>Non publié</em>
        <?php endif; ?>
    </p>

    <!-- ── Informations générales ──────────────────────────────────────────── -->
    <div class="section">
        <h2>Informations générales</h2>
        <dl>
            <dt>ID</dt>
            <dd><?= $article['id'] ?></dd>

            <dt>Slug</dt>
            <dd><code><?= htmlspecialchars($article['slug']) ?></code></dd>

            <dt>Catégorie</dt>
            <dd><?= $article['category_name'] ? htmlspecialchars($article['category_name']) : '<em>—</em>' ?></dd>

            <dt>Auteur</dt>
            <dd><?= htmlspecialchars($article['author_email']) ?></dd>

            <dt>Créé le</dt>
            <dd><?= fmt_date($article['created_at']) ?></dd>

            <dt>Publié le</dt>
            <dd><?= fmt_date($article['published_at'], 'Non planifié') ?></dd>

            <dt>Statut</dt>
            <dd><?= $article['is_active'] ? 'Actif' : 'Inactif' ?></dd>
        </dl>
    </div>

    <!-- ── Meta description ───────────────────────────────────────────────── -->
    <div class="section">
        <h2>Meta description</h2>
        <p><?= htmlspecialchars($article['meta_description']) ?></p>
    </div>

    <!-- ── Contenu ────────────────────────────────────────────────────────── -->
    <div class="section">
        <h2>Contenu</h2>
        <div class="content-block"><?= htmlspecialchars($article['content']) ?></div>
    </div>

    <!-- ── Images ─────────────────────────────────────────────────────────── -->
    <div class="section">
        <h2>Images (<?= count($images) ?>)</h2>

        <?php if (empty($images)): ?>
            <p class="no-images">Aucune image associée à cet article.</p>
        <?php else: ?>
            <div class="images-grid">
                <?php foreach ($images as $img): ?>
                    <figure>
                        <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="<?= htmlspecialchars($img['alt_text']) ?>" loading="lazy"
                            onerror="this.style.opacity='.3';this.title='Image introuvable';">
                        <figcaption>
                            #<?= $img['id'] ?><br>
                            <?= htmlspecialchars(basename($img['image_url'])) ?><br>
                            <small><?= fmt_date($img['created_at']) ?></small>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Actions ────────────────────────────────────────────────────────── -->
    <div class="actions">
        <a href="form/articles_create.php">+ Nouvel article</a>
        <a href="form/articles_create.php?id=<?= (int) $article['id'] ?>">Modifier cet article</a>
        <a href="articles_list.php">Liste des articles</a>
    </div>

</body>

</html>