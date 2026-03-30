<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/auth.php';

require_guest();

$error = $_GET['error'] ?? null;
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f6f8fb;
        }

        .card {
            width: 100%;
            max-width: 380px;
            background: #fff;
            border: 1px solid #dde3ea;
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.07);
        }

        h1 {
            margin-top: 0;
            font-size: 1.3rem;
        }

        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: 600;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #c9d3df;
            border-radius: 8px;
            padding: 10px;
            font-size: 1rem;
        }

        button {
            margin-top: 16px;
            width: 100%;
            padding: 11px;
            border: 0;
            border-radius: 8px;
            background: #0d6efd;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }

        .error {
            color: #a30000;
            background: #ffe9e9;
            border: 1px solid #ffc6c6;
            border-radius: 8px;
            padding: 8px 10px;
            margin-top: 10px;
            font-size: 0.95rem;
        }
    </style>
</head>

<body>
    <main class="card">
        <h1>Se connecter</h1>

        <?php if ($error === '1'): ?>
            <p class="error">Email ou mot de passe invalide.</p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required>

            <label for="password">Mot de passe</label>
            <input id="password" name="password" type="password" required>

            <button type="submit">Connexion</button>
        </form>
    </main>
</body>

</html>