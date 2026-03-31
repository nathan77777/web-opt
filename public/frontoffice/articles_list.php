<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/articles.php';

$articles = get_frontoffice_articles();

$page_title = 'Actualités sur la Guerre en Iran – Analyses & Dernières Nouvelles';
$page_description = 'Suivez toute l\'actualité sur la guerre en Iran : analyses géopolitiques, dernières nouvelles, reportages et décryptages en temps réel.';
$canonical_url = 'https://www.votre-site.com/guerre-iran';
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></title>

    <meta name="description" content="<?= htmlspecialchars($page_description, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8') ?>">

    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_description, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:locale" content="fr_FR">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_description, ENT_QUOTES, 'UTF-8') ?>">

    <link rel="stylesheet" href="../assets/css/frontoffice_article_list.css">
</head>

<body>
    <header role="banner">
        <div class="wrapper header">
            <h1>Actualités sur la Guerre en Iran</h1>
            <p class="header-subtitle">Analyses géopolitiques, reportages et dernières nouvelles en temps réel</p>
        </div>
    </header>

    <main class="wrapper" id="main-content">

        <?php if ($articles === []): ?>
            <p class="empty" role="status">Aucun article publié pour le moment.</p>
        <?php else: ?>
            <!-- Section avec un titre H2 décrivant le contenu de la liste -->
            <section aria-labelledby="articles-heading">
                <h2 id="articles-heading" class="sr-only">Liste des articles</h2>

                <div class="list">
                    <?php foreach ($articles as $article): ?>
                        <?php
                        $article_url = '/pages/article-' . rawurlencode((string) $article['slug']);
                        ?>
                        <article class="card"
                            aria-label="<?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?>">

                            <?php if (!empty($article['main_image_url'])): ?>
                                <a class="cover-link" href="<?= $article_url ?>" tabindex="-1" aria-hidden="true">
                                    <img class="cover"
                                        src="<?= htmlspecialchars((string) $article['main_image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars((string) $article['main_image_alt_text'], ENT_QUOTES, 'UTF-8') ?>"
                                        loading="lazy" width="800" height="450">
                                </a>
                            <?php endif; ?>

                            <!-- Catégorie visible par les moteurs de recherche -->
                            <span class="category">
                                <?= htmlspecialchars((string) ($article['category_name'] ?? 'Sans catégorie'), ENT_QUOTES, 'UTF-8') ?>
                            </span>

                            <!-- H3 pour les titres d'articles (H2 réservé à la section) -->
                            <h3 class="card-title">
                                <a class="title-link" href="<?= $article_url ?>">
                                    <?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </h3>

                            <!-- Date au format machine pour les moteurs de recherche -->
                            <?php if (!empty($article['published_at'])): ?>
                                <p class="card-date">
                                    <time
                                        datetime="<?= htmlspecialchars((string) $article['published_at'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?php
                                        try {
                                            $d = new DateTimeImmutable((string) $article['published_at']);
                                            echo htmlspecialchars($d->format('d/m/Y'), ENT_QUOTES, 'UTF-8');
                                        } catch (Exception) {
                                            echo htmlspecialchars((string) $article['published_at'], ENT_QUOTES, 'UTF-8');
                                        }
                                        ?>
                                    </time>
                                </p>
                            <?php endif; ?>

                            <p class="card-description">
                                <?= htmlspecialchars((string) $article['meta_description'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <a class="read-more" href="<?= $article_url ?>"
                                aria-label="Lire l'article : <?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?>">
                                Lire l'article
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>