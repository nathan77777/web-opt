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
            <h1>Frontoffice - Liste des articles</h1>
            <a class="back-link" href="/backoffice/">Connexion backoffice</a>
        </div>

        <p class="todo">Vue frontoffice de liste en place. Le detail d'article sera ajoute plus tard.</p>

        <?php if ($articles === []): ?>
            <p class="empty">Aucun article publie pour le moment.</p>
        <?php else: ?>
            <section class="list">
                <?php foreach ($articles as $article): ?>
                    <article class="card">
                        <span
                            class="category"><?= htmlspecialchars((string) ($article['category_name'] ?? 'Sans categorie'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2><?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="slug">Slug: <?= htmlspecialchars((string) $article['slug'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p><?= htmlspecialchars((string) $article['meta_description'], ENT_QUOTES, 'UTF-8') ?></p>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>