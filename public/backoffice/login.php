<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /backoffice/');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: /backoffice/?error=1');
    exit;
}

if (!attempt_login($email, $password)) {
    header('Location: /backoffice/?error=1');
    exit;
}

header('Location: /backoffice/articles_list.php');
exit;
