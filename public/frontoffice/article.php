<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/articles.php';

$pdo  = getConnection();
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($slug === '') {
    http_response_code(404);
    exit('Article introuvable.');
}

$article = getArticleBySlug($pdo, $slug);

if ($article === false) {
    http_response_code(404);
    exit('Article introuvable.');
}

$images   = getArticleImages($pdo, (int) $article['id']);
$title    = htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8');
$category = htmlspecialchars((string) ($article['category_name'] ?? 'Sans catégorie'), ENT_QUOTES, 'UTF-8');
$desc     = htmlspecialchars((string) $article['meta_description'], ENT_QUOTES, 'UTF-8');
$date     = $article['published_at']
    ? (new DateTimeImmutable($article['published_at']))->format('d/m/Y')
    : '';
$dateIso  = $article['published_at']
    ? (new DateTimeImmutable($article['published_at']))->format('Y-m-d')
    : '';
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?> — Guerre en Iran</title>
    <meta name="description" content="<?= $desc ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://iraninfo.local/guerre-en-iran/<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="/assets/css/frontoffice_index.css">

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Article",
            "headline": "<?= addslashes($article['title']) ?>",
        "description": "<?= addslashes($article['meta_description']) ?>",
        "datePublished": "<?= $dateIso ?>",
        "author": {
            "@type": "Person",
            "name": "<?= htmlspecialchars((string) $article['author_email'], ENT_QUOTES, 'UTF-8') ?>"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Iran Info"
        }
    }
    </script>
</head>

<body>
<header class="site-header">
    <div class="wrapper">
        <a href="/guerre-en-iran/">
            <h1>Iran Info</h1>
        </a>
        <p class="site-desc">Actualités et analyses sur la guerre en Iran</p>
    </div>
</header>

<main class="wrapper article-detail">

    <nav class="breadcrumb" aria-label="Fil d'ariane">
        <a href="/guerre-en-iran/">Guerre en Iran</a>
        <span aria-hidden="true"> › </span>
        <span><?= $title ?></span>
    </nav>

    <article>
        <header class="article-header">
            <span class="category"><?= $category ?></span>

            <?php if ($date): ?>
                <time class="date" datetime="<?= $dateIso ?>">
                    <?= $date ?>
                </time>
            <?php endif; ?>

            <h2><?= $title ?></h2>
            <p class="article-desc"><?= $desc ?></p>
        </header>

        <?php if (!empty($images)): ?>
            <figure class="article-figure">
                <img
                    src="<?= htmlspecialchars($images[0]['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                    alt="Illustration : <?= $title ?> — guerre en Iran"
                    width="1200"
                    height="630"
                    loading="eager"
                >
            </figure>
        <?php endif; ?>

        <div class="article-content">
            <?= nl2br(htmlspecialchars((string) $article['content'], ENT_QUOTES, 'UTF-8')) ?>
        </div>
    </article>

    <a class="back-link" href="/guerre-en-iran/">← Retour aux actualités sur la guerre en Iran</a>

</main>

<footer class="site-footer">
    <div class="wrapper">
        <p>&copy; <?= date('Y') ?> Iran Info — Toute l'actualité sur la guerre en Iran</p>
        <a href="/backoffice/">Espace administration</a>
    </div>
</footer>

</body>

</html>
