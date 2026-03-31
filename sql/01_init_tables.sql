CREATE TABLE
    IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT NOW ()
    );

CREATE TABLE
    categories (
        id SERIAL PRIMARY KEY,
        libelles VARCHAR(255) NOT NULL UNIQUE
    );

create table
    articles (
        id SERIAL PRIMARY KEY,
        category_id INTEGER,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content TEXT NOT NULL,
        meta_description VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        published_at TIMESTAMP DEFAULT NULL,
        author_id INTEGER NOT NULL,
        is_active BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL
    );

create table
    images (
        id SERIAL PRIMARY KEY,
        article_id INTEGER NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE
    );

-- Mot de passe en clair: admin123
INSERT INTO
    users (email, password_hash)
VALUES
    (
        'admin@example.com',
        '$2y$10$4YB/.OwAipMxapye5YKbkOwtGZ.1rxJWXDtBokNg.YKHHO7O7vhqO'
    ) ON CONFLICT (email) DO NOTHING;

INSERT INTO
    categories (libelles)
VALUES
    ('SEO'),
    ('Performance web'),
    ('Marketing digital') ON CONFLICT (libelles) DO NOTHING;

INSERT INTO
    articles (
        category_id,
        title,
        slug,
        content,
        meta_description,
        published_at,
        author_id,
        is_active
    )
SELECT
    c.id,
    v.title,
    v.slug,
    v.content,
    v.meta_description,
    v.published_at,
    u.id,
    v.is_active
FROM
    (
        VALUES
            (
                'SEO',
                '10 optimisations SEO techniques pour 2026',
                'optimisations-seo-techniques-2026',
                'Un guide pratique pour ameliorer le crawl, la vitesse et la structure semantique.',
                'Checklist SEO technique orientee performance pour 2026.',
                NOW () - INTERVAL '10 days',
                TRUE
            ),
            (
                'Performance web',
                'Comment reduire le TTFB sur un site PHP',
                'reduire-ttfb-site-php',
                'Les bonnes pratiques serveur, cache et base de donnees pour servir plus vite.',
                'Conseils concrets pour reduire le TTFB sur une stack PHP.',
                NOW () - INTERVAL '5 days',
                TRUE
            ),
            (
                'Marketing digital',
                'Construire un tunnel de conversion simple',
                'tunnel-conversion-simple',
                'Une methode pas a pas pour transformer les visiteurs en leads qualifies.',
                'Methode simple pour creer un tunnel de conversion efficace.',
                NULL,
                FALSE
            )
    ) AS v (
        category_name,
        title,
        slug,
        content,
        meta_description,
        published_at,
        is_active
    )
    INNER JOIN categories c ON c.libelles = v.category_name
    INNER JOIN users u ON u.email = 'admin@example.com'
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            articles a
        WHERE
            a.slug = v.slug
    );

INSERT INTO
    images (article_id, image_url)
SELECT
    a.id,
    v.image_url
FROM
    (
        VALUES
            (
                'optimisations-seo-techniques-2026',
                'https://images.unsplash.com/photo-1460925895917-afdab827c52f'
            ),
            (
                'reduire-ttfb-site-php',
                'https://images.unsplash.com/photo-1558494949-ef010cbdcc31'
            ),
            (
                'tunnel-conversion-simple',
                'https://images.unsplash.com/photo-1552664730-d307ca884978'
            )
    ) AS v (slug, image_url)
    INNER JOIN articles a ON a.slug = v.slug
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            images i
        WHERE
            i.article_id = a.id
            AND i.image_url = v.image_url
    );

ALTER TABLE images ADD COLUMN IF NOT EXISTS alt_text VARCHAR(255) DEFAULT 'Image article';

ALTER TABLE articles ADD COLUMN IF NOT EXISTS first_image_url VARCHAR(255) DEFAULT NULL;