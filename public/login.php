<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: index.php?error=1');
    exit;
}

if (!attempt_login($email, $password)) {
    header('Location: index.php?error=1');
    exit;
}

header('Location: dashboard.php');
exit;
