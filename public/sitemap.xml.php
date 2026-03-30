<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/articles.php';

$pdo      = getConnection();
$articles = getPublishedArticles($pdo);

header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    <url>
        <loc>https://iraninfo.local/guerre-en-iran/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <?php foreach ($articles as $article): ?>
        <url>
            <loc>https://iraninfo.local/guerre-en-iran/<?= htmlspecialchars((string) $article['slug'], ENT_QUOTES, 'UTF-8') ?></loc>
            <lastmod><?= (new DateTimeImmutable($article['published_at']))->format('Y-m-d') ?></lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    <?php endforeach; ?>

</urlset>