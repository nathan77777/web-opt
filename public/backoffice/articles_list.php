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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f3f5f9;
            color: #1f2a37;
        }

        .wrapper {
            max-width: 980px;
            margin: 40px auto;
            padding: 0 16px;
        }

        .panel {
            background: #fff;
            border: 1px solid #d9e1eb;
            border-radius: 10px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06);
            padding: 20px;
            overflow: hidden;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0;
            font-size: 1.5rem;
        }

        .subtitle {
            margin: 6px 0 0;
            color: #556172;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action,
        .logout {
            text-decoration: none;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 12px;
            color: #1f2937;
            font-weight: 600;
            background: #f8fafc;
            white-space: nowrap;
            font-size: 0.9rem;
        }

        .todo {
            margin: 0 0 12px;
            padding: 10px 12px;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            color: #475569;
            font-size: 0.92rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th,
        td {
            text-align: left;
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            font-size: 0.82rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: #4b5563;
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .active {
            background: #dcfce7;
            color: #166534;
        }

        .inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty {
            margin: 16px 0 0;
            padding: 12px;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            color: #4b5563;
            background: #f8fafc;
        }

        @media (max-width: 780px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead {
                display: none;
            }

            tr {
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                margin-bottom: 10px;
                overflow: hidden;
            }

            td {
                border-bottom: 1px solid #eef2f7;
            }

            td::before {
                content: attr(data-label);
                display: block;
                font-size: 0.75rem;
                color: #6b7280;
                text-transform: uppercase;
                margin-bottom: 4px;
            }
        }
    </style>
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
                    <a class="action" href="#" aria-disabled="true">Editer (a faire)</a>
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
                                <td><a href="article_details.php?id=<?= $article['id'] ?>">Details</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>
</body>

</html>