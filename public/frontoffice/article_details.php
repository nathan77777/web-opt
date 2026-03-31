<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/articles.php';

$slug = trim((string) ($_GET['slug'] ?? ''));

if ($slug === '') {
    header('Location: /guerre-iran');
    exit;
}

$article = get_frontoffice_article_by_slug($slug);

if ($article === null) {
    http_response_code(404);
}

/**
 * Render a publication date for frontoffice pages.
 */
function format_publication_date(?string $date): string
{
    if ($date === null || $date === '') {
        return 'Date non disponible';
    }

    try {
        $parsed = new DateTimeImmutable($date);
    } catch (Exception) {
        return 'Date non disponible';
    }

    return $parsed->format('d/m/Y à H:i');
}

/**
 * Return an ISO 8601 date string for the <time datetime=""> attribute.
 */
function format_datetime_attr(?string $date): string
{
    if ($date === null || $date === '') {
        return '';
    }
    try {
        return (new DateTimeImmutable($date))->format(DateTimeInterface::ATOM);
    } catch (Exception) {
        return '';
    }
}

$images = [];

if ($article !== null) {
    $images = get_article_images_by_article_id((int) $article['id']);
}

// ── SEO variables ──────────────────────────────────────────────────────────────
$site_name = 'Guerre Iran – Actualités';
$base_url = 'https://www.votre-site.com';
$canonical_url = $base_url . '/guerre-iran/' . rawurlencode($slug);

if ($article !== null) {
    $page_title = htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') . ' – ' . $site_name;
    $page_description = htmlspecialchars((string) $article['meta_description'], ENT_QUOTES, 'UTF-8');
    $og_image = !empty($article['main_image_url'])
        ? htmlspecialchars((string) $article['main_image_url'], ENT_QUOTES, 'UTF-8')
        : '';
    $published_iso = format_datetime_attr((string) ($article['published_at'] ?? ''));
} else {
    $page_title = 'Article introuvable – ' . $site_name;
    $page_description = 'L\'article demandé n\'existe pas ou n\'est pas publié.';
    $og_image = '';
    $published_iso = '';
}
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Titre SEO -->
    <title><?= $page_title ?></title>

    <!-- Méta SEO de base -->
    <meta name="description" content="<?= $page_description ?>">
    <meta name="robots" content="<?= $article !== null ? 'index, follow' : 'noindex, nofollow' ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8') ?>">

    <?php if ($article !== null && !empty($article['author_email'])): ?>
        <meta name="author" content="<?= htmlspecialchars((string) $article['author_email'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if (!empty($published_iso)): ?>
        <meta name="article:published_time" content="<?= htmlspecialchars($published_iso, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="<?= htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= $page_title ?>">
    <meta property="og:description" content="<?= $page_description ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:locale" content="fr_FR">
    <?php if (!empty($og_image)): ?>
        <meta property="og:image" content="<?= $og_image ?>">
    <?php endif; ?>
    <?php if (!empty($published_iso)): ?>
        <meta property="article:published_time" content="<?= htmlspecialchars($published_iso, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $page_title ?>">
    <meta name="twitter:description" content="<?= $page_description ?>">
    <?php if (!empty($og_image)): ?>
        <meta name="twitter:image" content="<?= $og_image ?>">
    <?php endif; ?>

    <?php if ($article !== null): ?>
        <!-- Données structurées JSON-LD (Article) -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "NewsArticle",
            "headline": <?= json_encode((string) $article['title'], JSON_UNESCAPED_UNICODE) ?>,
            "description": <?= json_encode((string) $article['meta_description'], JSON_UNESCAPED_UNICODE) ?>,
            "url": <?= json_encode($canonical_url) ?>,
            <?php if (!empty($published_iso)): ?>
                "datePublished": <?= json_encode($published_iso) ?>,
            <?php endif; ?>
            <?php if (!empty($og_image)): ?>
                "image": <?= json_encode((string) $article['main_image_url']) ?>,
            <?php endif; ?>
            <?php if (!empty($article['author_email'])): ?>
                "author": {
                    "@type": "Person",
                    "email": <?= json_encode((string) $article['author_email'], JSON_UNESCAPED_UNICODE) ?>
                },
            <?php endif; ?>
            "publisher": {
                "@type": "Organization",
                "name": <?= json_encode($site_name, JSON_UNESCAPED_UNICODE) ?>
            }
        }
        </script>
    <?php endif; ?>

    <link rel="stylesheet" href="../assets/css/frontoffice_article_details.css">
</head>

<body>
    <main class="wrapper" id="main-content">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb">
                <li><a href="/guerre-iran">Accueil</a></li>
                <?php if ($article !== null): ?>
                    <li aria-current="page"><?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?></li>
                <?php else: ?>
                    <li aria-current="page">Article introuvable</li>
                <?php endif; ?>
            </ol>
        </nav>

        <?php if ($article === null): ?>
            <!-- ── Page 404 ─────────────────────────────────────────────── -->
            <section class="not-found" aria-labelledby="notfound-heading">
                <h1 id="notfound-heading">Article introuvable</h1>
                <p>L'article demandé n'existe pas ou n'est pas publié.</p>
                <a href="/guerre-iran">← Retour à l'accueil</a>
            </section>

        <?php else: ?>
            <!-- ── Article principal ────────────────────────────────────── -->
            <article class="article" aria-labelledby="article-title">

                <!-- Catégorie (pas un titre de section, donc un span) -->
                <p class="category">
                    <?= htmlspecialchars((string) ($article['category_name'] ?? 'Sans catégorie'), ENT_QUOTES, 'UTF-8') ?>
                </p>

                <!-- H1 unique = titre de l'article -->
                <h1 id="article-title">
                    <?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?>
                </h1>

                <!-- Métadonnées de l'article : date au format machine -->
                <p class="meta">
                    Publié le
                    <time datetime="<?= htmlspecialchars($published_iso, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars(format_publication_date((string) ($article['published_at'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                    </time>
                    <?php if (!empty($article['author_email'])): ?>
                        · Par <span
                            class="author"><?= htmlspecialchars((string) $article['author_email'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </p>

                <!-- Image principale avec dimensions et fetchpriority pour le LCP -->
                <?php if (!empty($article['main_image_url'])): ?>
                    <figure class="hero-figure">
                        <img class="hero"
                            src="<?= htmlspecialchars((string) $article['main_image_url'], ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?>" loading="eager"
                            fetchpriority="high" width="1200" height="630">
                        <figcaption class="sr-only">
                            Image principale de l'article :
                            <?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?>
                        </figcaption>
                    </figure>
                <?php endif; ?>

                <!-- H2 : Résumé -->
                <section class="summary" aria-labelledby="summary-heading">
                    <h2 id="summary-heading">Résumé</h2>
                    <p><?= htmlspecialchars((string) $article['meta_description'], ENT_QUOTES, 'UTF-8') ?></p>
                </section>

                <!-- H2 : Contenu principal -->
                <section class="content" aria-labelledby="content-heading">
                    <h2 id="content-heading">Contenu</h2>
                    <div class="content-text">
                        <?= nl2br(htmlspecialchars((string) $article['content'], ENT_QUOTES, 'UTF-8')) ?>
                    </div>
                </section>

                <!-- H2 : Galerie d'images -->
                <section class="gallery" aria-labelledby="gallery-heading">
                    <h2 id="gallery-heading">Images de l'article</h2>
                    <?php if ($images === []): ?>
                        <p class="empty">Aucune image supplémentaire pour cet article.</p>
                    <?php else: ?>
                        <div class="images-grid">
                            <?php foreach ($images as $image): ?>
                                <figure class="image-card">
                                    <img src="<?= htmlspecialchars((string) $image['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars((string) ($image['alt_text'] ?? 'Image article'), ENT_QUOTES, 'UTF-8') ?>"
                                        loading="lazy" width="800" height="450">
                                    <figcaption>
                                        <?= htmlspecialchars((string) ($image['alt_text'] ?? 'Image article'), ENT_QUOTES, 'UTF-8') ?>
                                    </figcaption>
                                </figure>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

            </article>
        <?php endif; ?>
    </main>
</body>

</html>