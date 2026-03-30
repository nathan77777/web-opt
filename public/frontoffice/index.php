<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/articles.php';

$pdo      = getConnection();
$articles = getPublishedArticles($pdo);

foreach ($articles as &$article) {
    $article['images'] = getArticleImages($pdo, (int) $article['id']);
}
unset($article);
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guerre en Iran — Actualités et analyses en direct</title>
    <meta name="description" content="Suivez toute l'actualité sur la guerre en Iran : analyses, reportages et dernières informations en temps réel.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://iraninfo.local/">
    <link rel="stylesheet" href="/assets/css/frontoffice_index.css">
</head>

<body>
<header class="site-header">
    <div class="wrapper">
        <h1>Guerre en Iran</h1>
        <p class="site-desc">Actualités et analyses sur la guerre en Iran</p>
    </div>
</header>

<main class="wrapper">

    <?php if ($articles === []): ?>
        <p class="empty">Aucun article publié pour le moment.</p>
    <?php else: ?>
        <section class="articles-list" aria-label="Liste des articles">
            <?php foreach ($articles as $article): ?>
                <?php
                $image     = $article['images'][0] ?? null;
                $category  = htmlspecialchars((string) ($article['category_name'] ?? 'Sans catégorie'), ENT_QUOTES, 'UTF-8');
                $title     = htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8');
                $slug      = htmlspecialchars((string) $article['slug'], ENT_QUOTES, 'UTF-8');
                $desc      = htmlspecialchars((string) $article['meta_description'], ENT_QUOTES, 'UTF-8');
                $date      = $article['published_at']
                    ? (new DateTimeImmutable($article['published_at']))->format('d/m/Y')
                    : '';
                ?>
                <article class="card">
                    <?php if ($image): ?>
                        <a href="/guerre-en-iran/<?= $slug ?>" tabindex="-1" aria-hidden="true">
                            <img
                                    src="<?= htmlspecialchars($image['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                    alt="Illustration de l'article : <?= $title ?>"
                                    width="800"
                                    height="450"
                                    loading="lazy"
                            >
                        </a>
                    <?php endif; ?>

                    <div class="card-body">
                        <span class="category"><?= $category ?></span>

                        <?php if ($date): ?>
                            <time class="date" datetime="<?= (new DateTimeImmutable($article['published_at']))->format('Y-m-d') ?>">
                                <?= $date ?>
                            </time>
                        <?php endif; ?>

                        <h2>
                            <a href="/guerre-en-iran/<?= $slug ?>"><?= $title ?></a>
                        </h2>

                        <p class="desc"><?= $desc ?></p>

                        <a class="read-more" href="/guerre-en-iran/<?= $slug ?>">
                            Lire l'article sur <?= $category ?>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

</main>

<footer class="site-footer">
    <div class="wrapper">
        <p>&copy; <?= date('Y') ?> Iran Info — Toute l'actualité sur la guerre en Iran</p>
        <a href="/backoffice/">Espace administration</a>
    </div>
</footer>

</body>

</html>