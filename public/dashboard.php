<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/auth.php';

require_auth();

$email = $_SESSION['user_email'] ?? '';
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #eef2f7;
        }

        .panel {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border-radius: 10px;
            border: 1px solid #d9e1eb;
            padding: 24px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        }

        a {
            display: inline-block;
            margin-top: 14px;
            text-decoration: none;
            color: #0d6efd;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <main class="panel">
        <h1>Bienvenue</h1>
        <p>Connexion reussie pour l'utilisateur:
            <strong><?= htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8') ?></strong></p>
        <a href="logout.php">Se deconnecter</a>
    </main>
</body>

</html>