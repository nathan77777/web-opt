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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f8fafc;
            color: #0f172a;
        }

        .wrapper {
            max-width: 980px;
            margin: 40px auto;
            padding: 0 16px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
        }

        h1 {
            margin: 0;
            font-size: 1.6rem;
        }

        .back-link {
            text-decoration: none;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 12px;
            color: #1e293b;
            background: #fff;
            font-weight: 600;
        }

        .todo {
            margin: 0 0 14px;
            color: #475569;
            font-size: 0.95rem;
        }

        .list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 14px;
        }

        .card {
            background: #fff;
            border: 1px solid #dbe2ea;
            border-radius: 10px;
            padding: 14px;
        }

        .category {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            background: #e2e8f0;
            color: #334155;
        }

        .slug {
            color: #64748b;
            font-size: 0.85rem;
        }

        .empty {
            margin-top: 10px;
            padding: 12px;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            color: #475569;
            background: #fff;
        }
    </style>
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