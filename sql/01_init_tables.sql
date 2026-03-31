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


ALTER TABLE images ADD COLUMN IF NOT EXISTS alt_text VARCHAR(255) DEFAULT 'Image article';

ALTER TABLE articles ADD COLUMN IF NOT EXISTS first_image_url VARCHAR(255) DEFAULT NULL;