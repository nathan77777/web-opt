<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/articles.php';

$slug = trim((string) ($_GET['slug'] ?? ''));

if ($slug === '') {
    header('Location: /frontoffice/');
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

$images = [];

if ($article !== null) {
    $images = get_article_images_by_article_id((int) $article['id']);
}
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?php if ($article !== null): ?>
            <?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?> - Frontoffice
        <?php else: ?>
            Article introuvable - Frontoffice
        <?php endif; ?>
    </title>
    <?php if ($article !== null): ?>
        <meta name="description"
            content="<?= htmlspecialchars((string) $article['meta_description'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/frontoffice_article_details.css">
</head>

<body>
    <main class="wrapper">
        <a class="back-link" href="/frontoffice/">&larr; Retour a la liste des articles</a>

        <?php if ($article === null): ?>
            <section class="not-found">
                <h1>Article introuvable</h1>
                <p>L'article demande n'existe pas ou n'est pas publie.</p>
            </section>
        <?php else: ?>
            <article class="article">
                <p class="category">
                    <?= htmlspecialchars((string) ($article['category_name'] ?? 'Sans categorie'), ENT_QUOTES, 'UTF-8') ?>
                </p>
                <h1><?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?></h1>

                <p class="meta">
                    Publie le
                    <?= htmlspecialchars(format_publication_date((string) ($article['published_at'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                    <?php if (!empty($article['author_email'])): ?>
                        · Par <?= htmlspecialchars((string) $article['author_email'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </p>

                <?php if (!empty($article['main_image_url'])): ?>
                    <img class="hero" src="<?= htmlspecialchars((string) $article['main_image_url'], ENT_QUOTES, 'UTF-8') ?>"
                        alt="Image principale de <?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?>"
                        loading="eager">
                <?php endif; ?>

                <section class="summary">
                    <h2>Resume</h2>
                    <p><?= htmlspecialchars((string) $article['meta_description'], ENT_QUOTES, 'UTF-8') ?></p>
                </section>

                <section class="content">
                    <h2>Contenu</h2>
                    <div class="content-text">
                        <?= nl2br(htmlspecialchars((string) $article['content'], ENT_QUOTES, 'UTF-8')) ?></div>
                </section>

                <section class="gallery">
                    <h2>Images de l'article</h2>
                    <?php if ($images === []): ?>
                        <p class="empty">Aucune image supplementaire pour cet article.</p>
                    <?php else: ?>
                        <div class="images-grid">
                            <?php foreach ($images as $image): ?>
                                <figure class="image-card">
                                    <img src="<?= htmlspecialchars((string) $image['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars((string) ($image['alt_text'] ?? 'Image article'), ENT_QUOTES, 'UTF-8') ?>"
                                        loading="lazy">
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