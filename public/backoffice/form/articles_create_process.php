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

$article_id = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
$is_edit_mode = $article_id !== null && $article_id !== false;

// ── Sanitize & validate inputs ────────────────────────────────────────────────

$title = trim($_POST['title'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$content = trim($_POST['content'] ?? '');
$meta_description = trim($_POST['meta_description'] ?? '');
$category_id = $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
$published_at = trim($_POST['published_at'] ?? '');
$is_active = isset($_POST['is_active']) ? true : false;
$main_image_index_raw = $_POST['main_image_index'] ?? '0';
$main_image_index = filter_var($main_image_index_raw, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 0],
]);

$errors = [];

if ($main_image_index === false) {
    $errors[] = 'Le choix de l\'image principale est invalide.';
}

$pdo = getConnection();

if ($is_edit_mode) {
    $existing_article = getArticleById($pdo, (int) $article_id);
    if (!$existing_article) {
        $_SESSION['form_errors'] = ['Article introuvable.'];
        header('Location: ../articles_list.php');
        exit;
    }
}

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

// ── Image compression settings ───────────────────────────────────────────────

const MAX_IMAGE_WIDTH = 1920; // px — images wider than this are downscaled
const MAX_IMAGE_HEIGHT = 1080; // px
const JPEG_QUALITY = 82;   // 0–100  (82 is a good size/quality balance)
const PNG_COMPRESSION = 6;    // 0–9    (zlib level; 6 = default)
const WEBP_QUALITY = 82;   // 0–100

/**
 * Compress and optionally resize an uploaded image in-place (overwrites $dest).
 *
 * - JPEG / WEBP: re-encoded at the configured quality level.
 * - PNG         : re-encoded with the configured zlib compression level.
 * - GIF         : left untouched (GD strips animation; skip recompression).
 *
 * Returns true on success, false if GD is unavailable or the operation fails.
 * On failure the original file at $dest is preserved.
 */
function compressImage(string $dest, string $mime): bool
{
    if (!extension_loaded('gd')) {
        return false;
    }

    $src = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($dest),
        'image/png' => @imagecreatefrompng($dest),
        'image/webp' => @imagecreatefromwebp($dest),
        default => false,   // GIF: skip
    };

    if ($src === false) {
        return false;
    }

    // ── Resize if the image exceeds the configured dimensions ────────────────
    $orig_w = imagesx($src);
    $orig_h = imagesy($src);

    $ratio = min(MAX_IMAGE_WIDTH / $orig_w, MAX_IMAGE_HEIGHT / $orig_h, 1.0);
    $new_w = (int) round($orig_w * $ratio);
    $new_h = (int) round($orig_h * $ratio);

    if ($new_w !== $orig_w || $new_h !== $orig_h) {
        $resized = imagecreatetruecolor($new_w, $new_h);

        // Preserve alpha channel for PNG
        if ($mime === 'image/png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        imagecopyresampled($resized, $src, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);
        imagedestroy($src);
        $src = $resized;
    }

    // ── Re-encode to the destination path ────────────────────────────────────
    $ok = match ($mime) {
        'image/jpeg' => imagejpeg($src, $dest, JPEG_QUALITY),
        'image/png' => imagepng($src, $dest, PNG_COMPRESSION),
        'image/webp' => imagewebp($src, $dest, WEBP_QUALITY),
        default => false,
    };

    imagedestroy($src);
    return $ok;
}

$uploaded_files = []; // Keyed by original input index; value = final relative URL path

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

        // Compress / resize the image (best-effort — keeps original if GD fails)
        compressImage($dest, $mime);

        $uploaded_files[$i] = UPLOAD_URL_BASE . $safe_name;
    }
}

$first_image_url = $is_edit_mode ? ($existing_article['first_image_url'] ?? null) : null;

if (!empty($uploaded_files)) {
    if (isset($uploaded_files[(int) $main_image_index])) {
        $first_image_url = $uploaded_files[(int) $main_image_index];
    } else {
        $first_image_url = reset($uploaded_files) ?: null;
        $errors[] = 'L\'image principale sélectionnée est invalide. La première image valide sera utilisée.';
    }
}

// ── If validation failed, flash errors and redirect back ──────────────────────

if (!empty($errors)) {
    foreach ($uploaded_files as $url) {
        $abs = __DIR__ . '/../../' . ltrim($url, '/');
        if (file_exists($abs)) {
            unlink($abs);
        }
    }

    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_old'] = [
        'title' => $title,
        'slug' => $slug,
        'content' => $content,
        'meta_description' => $meta_description,
        'category_id' => $category_id,
        'published_at' => $published_at,
        'is_active' => $is_active,
        'main_image_index' => $main_image_index_raw,
    ];
    $redirect = 'articles_create.php' . ($is_edit_mode ? '?id=' . (int) $article_id : '');
    header('Location: ' . $redirect);
    exit;
}

// ── Persist to database ───────────────────────────────────────────────────────

$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    if ($is_edit_mode) {
        $article_id = (int) $article_id;
        updateArticle($pdo, $article_id, [
            'category_id' => $category_id,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'meta_description' => $meta_description,
            'published_at' => $published_at_sql,
            'first_image_url' => $first_image_url,
            'is_active' => $is_active,
        ]);
    } else {
        $article_id = insertArticle($pdo, [
            'category_id' => $category_id,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'meta_description' => $meta_description,
            'published_at' => $published_at_sql,
            'first_image_url' => $first_image_url,
            'author_id' => $user_id,
            'is_active' => $is_active,
        ]);
    }

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
        'main_image_index' => $main_image_index_raw,
    ];
    $redirect = 'articles_create.php' . ($is_edit_mode ? '?id=' . (int) $article_id : '');
    header('Location: ' . $redirect);
    exit;
}

// ── Success ───────────────────────────────────────────────────────────────────

$_SESSION['flash_success'] = $is_edit_mode
    ? 'Article mis à jour avec succès.'
    : 'Article créé avec succès.';
header('Location: ../articles_list.php');
exit;