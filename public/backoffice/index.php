<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/auth.php';

require_guest();

$error = $_GET['error'] ?? null;
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Backoffice - Connexion</title>
    <link rel="stylesheet" href="../assets/css/backoffice_index.css">
</head>

<body>
    <main class="card">
        <h1>Connexion backoffice</h1>

        <?php if ($error === '1'): ?>
            <p class="error">Email ou mot de passe invalide.</p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="admin@example.com" required>

            <label for="password">Mot de passe</label>
            <input id="password" name="password" type="password" value="admin123" required>

            <button type="submit">Connexion</button>
        </form>

        <a class="front-link" href="/frontoffice/">Voir le frontoffice</a>
    </main>
</body>

</html>