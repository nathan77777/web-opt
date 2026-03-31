<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/articles.php';

require_auth();

$email = (string) ($_SESSION['user_email'] ?? '');
$articles = get_articles_with_categories();
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Backoffice - Articles</title>

    <!-- Preconnect pour réduire la latence DNS/TLS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Chargement non-bloquant des polices avec font-display=swap -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Syne:wght@700&family=DM+Sans:wght@400;500;600&display=swap"
          media="print"
          onload="this.media='all'">
    <noscript>
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css2?family=Syne:wght@700&family=DM+Sans:wght@400;500;600&display=swap">
    </noscript>

    <link rel="stylesheet" href="../assets/css/backoffice_article_list.css">
</head>

<body>
    <div class="wrapper">
        <section class="panel">
            <div class="header">
                <div>
                    <h1>Backoffice - Liste des articles</h1>
                    <p class="subtitle">
                        Connecté en tant que
                        <strong><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></strong>
                    </p>
                </div>
                <div class="actions">
                    <a class="action" href="form/articles_create.php">Créer</a>
                    <a class="logout" href="logout.php">Se déconnecter</a>
                </div>
            </div>

            <p class="todo">CRUD marqué en attente : la structure est prête, les actions seront implémentées ensuite.</p>

            <?php if ($articles === []): ?>
                <p class="empty">Aucun article pour le moment.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Auteur</th>
                            <th>Publication</th>
                            <th>État</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td data-label="#"><?= (int) $article['id'] ?></td>
                                <td data-label="Titre">
                                    <strong><?= htmlspecialchars((string) $article['title'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <small><?= htmlspecialchars((string) $article['slug'], ENT_QUOTES, 'UTF-8') ?></small>
                                </td>
                                <td data-label="Catégorie">
                                    <?= htmlspecialchars((string) ($article['category_name'] ?? 'Sans catégorie'), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td data-label="Auteur">
                                    <?= htmlspecialchars((string) ($article['author_email'] ?? 'Inconnu'), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td data-label="Publication">
                                    <?= htmlspecialchars((string) ($article['published_at'] ?? 'Non publié'), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td data-label="État">
                                    <?php if ((string) $article['is_active'] === 't'): ?>
                                        <span class="badge active">Actif</span>
                                    <?php else: ?>
                                        <span class="badge inactive">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="article_details.php?id=<?= (int) $article['id'] ?>">Détails</a>
                                    |
                                    <a href="form/articles_create.php?id=<?= (int) $article['id'] ?>">Éditer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>
</body>

</html>