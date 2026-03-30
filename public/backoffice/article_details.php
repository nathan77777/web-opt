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
    <style>
        body {
            font-family: sans-serif;
            max-width: 860px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        h1 {
            margin-bottom: .25rem;
        }

        .meta {
            color: #555;
            font-size: .9rem;
            margin-bottom: 1.5rem;
        }

        .badge {
            display: inline-block;
            padding: .2rem .6rem;
            border-radius: 3px;
            font-size: .8rem;
            font-weight: bold;
        }

        .badge-on {
            background: #d4edda;
            color: #155724;
        }

        .badge-off {
            background: #f8d7da;
            color: #721c24;
        }

        .section {
            margin-top: 1.5rem;
        }

        .section h2 {
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #888;
            border-bottom: 1px solid #ddd;
            padding-bottom: .3rem;
        }

        .content-block {
            white-space: pre-wrap;
            background: #f9f9f9;
            border: 1px solid #eee;
            padding: 1rem;
            border-radius: 4px;
            font-size: .95rem;
        }

        .images-grid {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            margin-top: .75rem;
        }

        .images-grid figure {
            margin: 0;
            text-align: center;
        }

        .images-grid img {
            max-width: 200px;
            max-height: 150px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: block;
        }

        .images-grid figcaption {
            font-size: .75rem;
            color: #777;
            margin-top: .3rem;
            max-width: 200px;
            word-break: break-all;
        }

        .no-images {
            color: #888;
            font-style: italic;
        }

        .actions {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .flash {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: .75rem 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        dl {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: .4rem 1rem;
        }

        dt {
            font-weight: bold;
            color: #444;
        }

        dd {
            margin: 0;
        }
    </style>
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
                        <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="Image article" loading="lazy"
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
        <a href="articles_list.php">Liste des articles</a>
    </div>

</body>

</html>