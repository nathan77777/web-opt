<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

function require_guest(): void
{
    if (!empty($_SESSION['user_id'])) {
        header('Location: dashboard.php');
        exit;
    }
}

function require_auth(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

function attempt_login(string $email, string $password): bool
{
    $connection = db_connect();

    $query = 'SELECT id, email, password_hash FROM users WHERE email = $1 LIMIT 1';
    $result = pg_query_params($connection, $query, [$email]);

    if ($result === false) {
        return false;
    }

    $user = pg_fetch_assoc($result);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_email'] = $user['email'];

    return true;
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
