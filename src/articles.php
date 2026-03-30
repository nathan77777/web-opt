<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

/**
 * @return array<int, array<string, mixed>>
 */
function get_articles_with_categories(): array
{
    $connection = db_connect();

    $query = <<<'SQL'
        SELECT
            a.id,
            a.title,
            a.slug,
            a.is_active,
            a.published_at,
            c.libelles AS category_name,
            u.email AS author_email
        FROM articles a
        LEFT JOIN categories c ON c.id = a.category_id
        LEFT JOIN users u ON u.id = a.author_id
        ORDER BY a.created_at DESC, a.id DESC
    SQL;

    $result = pg_query($connection, $query);

    if ($result === false) {
        return [];
    }

    return pg_fetch_all($result) ?: [];
}

/**
 * @return array<int, array<string, mixed>>
 */
function get_frontoffice_articles(): array
{
    $connection = db_connect();

    $query = <<<'SQL'
        SELECT
            a.id,
            a.title,
            a.slug,
            a.meta_description,
            c.libelles AS category_name
        FROM articles a
        LEFT JOIN categories c ON c.id = a.category_id
        WHERE a.is_active = TRUE
          AND a.published_at IS NOT NULL
        ORDER BY a.published_at DESC, a.id DESC
    SQL;

    $result = pg_query($connection, $query);

    if ($result === false) {
        return [];
    }

    return pg_fetch_all($result) ?: [];
}


/**
 * Fetch all published articles for the frontoffice.
 */
function getPublishedArticles(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT a.*, c.libelles AS category_name
        FROM articles a
        LEFT JOIN categories c ON c.id = a.category_id
        WHERE a.is_active = TRUE AND a.published_at IS NOT NULL AND a.published_at <= NOW()
        ORDER BY a.published_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch all articles for the backoffice (no filter).
 */
function getAllArticles(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT a.*, c.libelles AS category_name
        FROM articles a
        LEFT JOIN categories c ON c.id = a.category_id
        ORDER BY a.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch a single article by id, with category and author info.
 * Returns false if not found.
 */
function getArticleById(PDO $pdo, int $id): array|false
{
    $stmt = $pdo->prepare("
        SELECT a.*,
               c.libelles AS category_name,
               u.email    AS author_email
        FROM articles a
        LEFT JOIN categories c ON c.id = a.category_id
        LEFT JOIN users      u ON u.id = a.author_id
        WHERE a.id = :id
    ");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Fetch all images linked to a given article.
 */
function getArticleImages(PDO $pdo, int $article_id): array
{
    $stmt = $pdo->prepare("
        SELECT id, image_url, created_at
        FROM images
        WHERE article_id = :article_id
        ORDER BY created_at ASC
    ");
    $stmt->execute([':article_id' => $article_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/**
 * Insert a new article and return its new id.
 *
 * @param PDO   $pdo
 * @param array $data {
 *   category_id      : int|null,
 *   title            : string,
 *   slug             : string,
 *   content          : string,
 *   meta_description : string,
 *   published_at     : string|null  (SQL datetime or null),
 *   author_id        : int,
 *   is_active        : bool,
 * }
 * @return int  The id of the newly created article.
 */
function insertArticle(PDO $pdo, array $data): int
{
    $stmt = $pdo->prepare("
        INSERT INTO articles
            (category_id, title, slug, content, meta_description, published_at, author_id, is_active)
        VALUES
            (:category_id, :title, :slug, :content, :meta_description, :published_at, :author_id, :is_active)
        RETURNING id
    ");

    $stmt->execute([
        ':category_id' => $data['category_id'],
        ':title' => $data['title'],
        ':slug' => $data['slug'],
        ':content' => $data['content'],
        ':meta_description' => $data['meta_description'],
        ':published_at' => $data['published_at'],
        ':author_id' => $data['author_id'],
        ':is_active' => $data['is_active'] ? 'true' : 'false',
    ]);

    return (int) $stmt->fetchColumn();
}

/**
 * Update an existing article.
 *
 * @param PDO   $pdo
 * @param int   $article_id
 * @param array $data {
 *   category_id      : int|null,
 *   title            : string,
 *   slug             : string,
 *   content          : string,
 *   meta_description : string,
 *   published_at     : string|null  (SQL datetime or null),
 *   is_active        : bool,
 * }
 */
function updateArticle(PDO $pdo, int $article_id, array $data): void
{
    $stmt = $pdo->prepare("
        UPDATE articles
        SET
            category_id = :category_id,
            title = :title,
            slug = :slug,
            content = :content,
            meta_description = :meta_description,
            published_at = :published_at,
            is_active = :is_active
        WHERE id = :id
    ");

    $stmt->execute([
        ':category_id' => $data['category_id'],
        ':title' => $data['title'],
        ':slug' => $data['slug'],
        ':content' => $data['content'],
        ':meta_description' => $data['meta_description'],
        ':published_at' => $data['published_at'],
        ':is_active' => $data['is_active'] ? 'true' : 'false',
        ':id' => $article_id,
    ]);
}

/**
 * Insert one image row linked to an article.
 *
 * @param PDO    $pdo
 * @param int    $article_id
 * @param string $image_url   Relative URL stored in DB, e.g. /uploads/articles/abc123.jpg
 */
function insertArticleImage(PDO $pdo, int $article_id, string $image_url): void
{
    $stmt = $pdo->prepare("
        INSERT INTO images (article_id, image_url)
        VALUES (:article_id, :image_url)
    ");
    $stmt->execute([
        ':article_id' => $article_id,
        ':image_url' => $image_url,
    ]);
}

function getArticleBySlug(PDO $pdo, string $slug): array|false
{
    $stmt = $pdo->prepare("
        SELECT a.*,
               c.libelles AS category_name,
               u.email    AS author_email
        FROM articles a
        LEFT JOIN categories c ON c.id = a.category_id
        LEFT JOIN users      u ON u.id = a.author_id
        WHERE a.slug = :slug
          AND a.is_active = TRUE
          AND a.published_at IS NOT NULL
          AND a.published_at <= NOW()
    ");
    $stmt->execute([':slug' => $slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
