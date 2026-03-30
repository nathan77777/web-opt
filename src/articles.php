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
