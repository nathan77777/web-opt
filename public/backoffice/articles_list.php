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
    <link rel="stylesheet" href="../assets/css/article_list.css">
</head>

<body>
    <div class="wrapper">
        <section class="panel">
            <div class="header">
                <div>
                    <h1>Backoffice - Liste des articles</h1>
                    <p class="subtitle">
                        Connecte en tant que
                        <strong><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></strong>
                    </p>
                </div>
                <div class="actions">
                    <a class="action" href="form/articles_create.php">Creer</a>
                    <a class="action" href="#" aria-disabled="true">Supprimer (a faire)</a>
                    <a class="logout" href="logout.php">Se deconnecter</a>
                </div>
            </div>

            <p class="todo">CRUD marque en attente: la structure est prete, les actions seront implementees ensuite.</p>

            <?php if ($articles === []): ?>
                <p class="empty">Aucun article pour le moment.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Titre</th>
                            <th>Categorie</th>
                            <th>Auteur</th>
                            <th>Publication</th>
                            <th>Etat</th>
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
                                <td data-label="Categorie">
                                    <?= htmlspecialchars((string) ($article['category_name'] ?? 'Sans categorie'), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td data-label="Auteur">
                                    <?= htmlspecialchars((string) ($article['author_email'] ?? 'Inconnu'), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td data-label="Publication">
                                    <?= htmlspecialchars((string) ($article['published_at'] ?? 'Non publie'), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td data-label="Etat">
                                    <?php if ((string) $article['is_active'] === 't'): ?>
                                        <span class="badge active">Actif</span>
                                    <?php else: ?>
                                        <span class="badge inactive">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="article_details.php?id=<?= $article['id'] ?>">Details</a>
                                    |
                                    <a href="form/articles_create.php?id=<?= $article['id'] ?>">Editer</a>
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