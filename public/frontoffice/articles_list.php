<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/articles.php';

$articles = get_frontoffice_articles();
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Frontoffice - Articles</title>
    <link rel="stylesheet" href="../assets/css/frontoffice_index.css">
</head>

<body>
    <main class="wrapper">
        <div class="header">
            <h1>Actualites sur la Guerre en Iran</h1>
            <a class="back-link" href="/backoffice/">Connexion backoffice</a>
        </div>

        <?php if ($articles === []): ?>
            <p class="empty">Aucun article publie pour le moment.</p>
        <?php else: ?>
            <section class="list">
                <?php foreach ($articles as $article): ?>
                    <article class="card">
                        <?php if (!empty($article['main_image_url'])): ?>
                            <a class="cover-link" href="/pages/article-<?= rawurlencode((string) $article['slug']) ?>">
                                <img class="cover"
                                    src="<?= htmlspecialchars((string) $article['main_image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                    alt="Image principale de <?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?>"
                                    loading="lazy">
                            </a>
                        <?php endif; ?>

                        <span
                            class="category"><?= htmlspecialchars((string) ($article['category_name'] ?? 'Sans categorie'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2>
                            <a class="title-link" href="/pages/article-<?= rawurlencode((string) $article['slug']) ?>">
                                <?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h2>
                        <p class="slug">Slug: <?= htmlspecialchars((string) $article['slug'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p><?= htmlspecialchars((string) $article['meta_description'], ENT_QUOTES, 'UTF-8') ?></p>
                        <a class="read-more" href="/pages/article-<?= rawurlencode((string) $article['slug']) ?>">Lire
                            l'article</a>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>