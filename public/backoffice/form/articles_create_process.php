<?php
session_start();

require_once __DIR__ . '/../../../src/auth.php';
require_once __DIR__ . '/../../../src/database.php';
require_once __DIR__ . '/../../../src/articles.php';

require_auth();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: articles_create.php');
    exit;
}

// ── Sanitize & validate inputs ────────────────────────────────────────────────

$title = trim($_POST['title'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$content = trim($_POST['content'] ?? '');
$meta_description = trim($_POST['meta_description'] ?? '');
$category_id = $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
$published_at = trim($_POST['published_at'] ?? '');
$is_active = isset($_POST['is_active']) ? true : false;

$errors = [];

if ($title === '') {
    $errors[] = 'Le titre est obligatoire.';
}
if ($slug === '') {
    $errors[] = 'Le slug est obligatoire.';
} elseif (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
    $errors[] = 'Le slug ne doit contenir que des minuscules, chiffres et tirets.';
}
if ($content === '') {
    $errors[] = 'Le contenu est obligatoire.';
}
if ($meta_description === '') {
    $errors[] = 'La meta description est obligatoire.';
}

// Validate published_at format if provided
$published_at_sql = null;
if ($published_at !== '') {
    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $published_at);
    if ($dt === false) {
        $errors[] = 'Format de date de publication invalide.';
    } else {
        $published_at_sql = $dt->format('Y-m-d H:i:s');
    }
}

// ── Image upload validation ───────────────────────────────────────────────────

const UPLOAD_DIR = __DIR__ . '/../../../uploads/articles/';
const UPLOAD_URL_BASE = '/uploads/articles/';
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB
const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

$uploaded_files = []; // Will hold final relative URL paths

if (!empty($_FILES['images']['name'][0])) {
    // Ensure upload directory exists
    if (!is_dir(UPLOAD_DIR) && !@mkdir(UPLOAD_DIR, 0755, true) && !is_dir(UPLOAD_DIR)) {
        $errors[] = 'Le dossier de televersement est inaccessible. Contactez un administrateur.';
    }

    if (empty($errors) && !is_writable(UPLOAD_DIR)) {
        $errors[] = 'Le dossier de televersement n\'est pas accessible en ecriture.';
    }

    $file_count = empty($errors) ? count($_FILES['images']['name']) : 0;

    for ($i = 0; $i < $file_count; $i++) {
        $file_error = $_FILES['images']['error'][$i];
        $file_tmp = $_FILES['images']['tmp_name'][$i];
        $file_name_raw = $_FILES['images']['name'][$i];
        $file_size = $_FILES['images']['size'][$i];

        if ($file_error === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($file_error !== UPLOAD_ERR_OK) {
            $errors[] = "Erreur lors de l'upload du fichier « $file_name_raw » (code $file_error).";
            continue;
        }
        if ($file_size > MAX_FILE_SIZE) {
            $errors[] = "Le fichier « $file_name_raw » dépasse la taille maximale de 5 Mo.";
            continue;
        }

        // Check MIME via finfo (more reliable than extension or $_FILES['type'])
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file_tmp);
        if (!in_array($mime, ALLOWED_MIME, true)) {
            $errors[] = "Le fichier « $file_name_raw » n'est pas une image autorisée ($mime).";
            continue;
        }

        // Build a safe unique filename
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        };
        $safe_name = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = UPLOAD_DIR . $safe_name;

        if (!move_uploaded_file($file_tmp, $dest)) {
            $errors[] = "Impossible de déplacer le fichier « $file_name_raw » sur le serveur.";
            continue;
        }

        $uploaded_files[] = UPLOAD_URL_BASE . $safe_name;
    }
}

// ── If validation failed, flash errors and redirect back ──────────────────────

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_old'] = [
        'title' => $title,
        'slug' => $slug,
        'content' => $content,
        'meta_description' => $meta_description,
        'category_id' => $category_id,
        'published_at' => $published_at,
        'is_active' => $is_active,
    ];
    header('Location: articles_create.php');
    exit;
}

// ── Persist to database ───────────────────────────────────────────────────────

$pdo = getConnection();
$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    $article_id = insertArticle($pdo, [
        'category_id' => $category_id,
        'title' => $title,
        'slug' => $slug,
        'content' => $content,
        'meta_description' => $meta_description,
        'published_at' => $published_at_sql,
        'author_id' => $user_id,
        'is_active' => $is_active,
    ]);

    foreach ($uploaded_files as $url) {
        insertArticleImage($pdo, $article_id, $url);
    }

    $pdo->commit();

} catch (PDOException $e) {
    $pdo->rollBack();

    // Remove already-moved files to avoid orphans
    foreach ($uploaded_files as $url) {
        $abs = __DIR__ . '/../../' . ltrim($url, '/');
        if (file_exists($abs)) {
            unlink($abs);
        }
    }

    if ($e->getCode() === '23505') { // unique_violation in PostgreSQL
        $_SESSION['form_errors'] = ['Ce slug est déjà utilisé par un autre article.'];
    } else {
        $_SESSION['form_errors'] = ['Erreur base de données : ' . $e->getMessage()];
    }
    $_SESSION['form_old'] = [
        'title' => $title,
        'slug' => $slug,
        'content' => $content,
        'meta_description' => $meta_description,
        'category_id' => $category_id,
        'published_at' => $published_at,
        'is_active' => $is_active,
    ];
    header('Location: articles_create.php');
    exit;
}

// ── Success ───────────────────────────────────────────────────────────────────

$_SESSION['flash_success'] = 'Article créé avec succès.';
header('Location: ../articles_list.php');
exit;