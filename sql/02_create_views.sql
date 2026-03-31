CREATE
OR REPLACE VIEW articles_with_category AS
SELECT
    a.id,
    a.title,
    a.slug,
    a.is_active,
    a.published_at,
    c.libelles AS category_name,
    u.email AS author_email
FROM
    articles a
    LEFT JOIN categories c ON c.id = a.category_id
    LEFT JOIN users u ON u.id = a.author_id
ORDER BY
    a.created_at DESC,
    a.id DESC;

CREATE
OR REPLACE VIEW frontoffice_articles AS
SELECT
    a.id,
    a.title,
    a.slug,
    a.meta_description,
    -- URL image principale
    COALESCE(
        NULLIF(a.first_image_url, ''),
        (
            SELECT
                i.image_url
            FROM
                images i
            WHERE
                i.article_id = a.id
            ORDER BY
                i.created_at ASC,
                i.id ASC
            LIMIT
                1
        )
    ) AS main_image_url,
    (
        SELECT
            i.alt_text
        FROM
            images i
        WHERE
            i.article_id = a.id
        ORDER BY
            i.created_at ASC,
            i.id ASC
        LIMIT
            1
    ) AS main_image_alt_text,
    c.libelles AS category_name,
    a.published_at
FROM
    articles a
    LEFT JOIN categories c ON c.id = a.category_id
WHERE
    a.is_active = TRUE
    AND a.published_at IS NOT NULL
    AND a.published_at <= NOW ()
ORDER BY
    a.published_at DESC,
    a.id DESC;