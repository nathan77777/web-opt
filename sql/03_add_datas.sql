-- =========================
-- CATEGORIES
-- =========================
INSERT INTO
    categories (libelles)
VALUES
    ('Militaire'),
    ('Diplomatie'),
    ('Economie'),
    ('International') ON CONFLICT (libelles) DO NOTHING;

-- =========================
-- ARTICLES
-- =========================
INSERT INTO
    articles (
        category_id,
        title,
        slug,
        content,
        meta_description,
        author_id,
        is_active,
        published_at,
        first_image_url
    )
VALUES
    -- 1
    (
        1,
        'Escalade des frappes sur les infrastructures énergétiques',
        'escalade-frappes-infrastructures-iran-2026',
        'Les tensions entre l’Iran et ses adversaires se sont intensifiées avec des frappes ciblant des infrastructures énergétiques stratégiques. Ces attaques affectent fortement la production et les marchés internationaux du pétrole.',
        'Escalade militaire en Iran avec des frappes sur les infrastructures énergétiques.',
        1,
        TRUE,
        NOW (),
        'https://example.com/images/iran-energy.jpg'
    ),
    -- 2
    (
        1,
        'Missiles iraniens lancés vers Israël',
        'missiles-iraniens-israel-2026',
        'L’Iran a lancé une nouvelle vague de missiles en direction d’Israël, touchant plusieurs zones urbaines. Les autorités israéliennes rapportent plusieurs blessés et des dégâts matériels importants.',
        'Attaques de missiles iraniens contre Israël en pleine escalade du conflit.',
        1,
        TRUE,
        NOW (),
        'https://example.com/images/missiles.jpg'
    ),
    -- 3
    (
        2,
        'Le G7 appelle à la fin des attaques contre les civils',
        'g7-appel-fin-attaques-civils-iran',
        'Les ministres des affaires étrangères du G7 ont publié une déclaration conjointe demandant un arrêt immédiat des attaques visant les populations civiles dans la région.',
        'Le G7 appelle à protéger les civils dans le conflit iranien.',
        1,
        TRUE,
        NOW (),
        'https://example.com/images/g7.jpg'
    ),
    -- 4
    (
        2,
        'Négociations tendues entre les États-Unis et l’Iran',
        'negociations-usa-iran-2026',
        'Des discussions diplomatiques sont en cours entre les États-Unis et l’Iran afin de limiter l’escalade militaire. Cependant, les tensions restent élevées et les progrès sont incertains.',
        'Tentatives diplomatiques pour réduire les tensions entre les USA et l’Iran.',
        1,
        TRUE,
        NOW (),
        'https://example.com/images/diplomacy.jpg'
    ),
    -- 5
    (
        3,
        'Impact du conflit sur les prix du pétrole',
        'impact-guerre-iran-prix-petrole',
        'Le conflit a provoqué une hausse significative des prix du pétrole sur les marchés internationaux, alimentant des inquiétudes sur l’économie mondiale.',
        'La guerre en Iran fait grimper les prix du pétrole.',
        1,
        TRUE,
        NOW (),
        'https://example.com/images/oil.jpg'
    ),
    -- 6
    (
        4,
        'Risque d’extension régionale du conflit',
        'extension-regionale-conflit-iran',
        'Plusieurs analystes mettent en garde contre une extension du conflit à l’ensemble du Moyen-Orient, impliquant de nouveaux acteurs régionaux.',
        'Le conflit iranien pourrait s’étendre à toute la région.',
        1,
        TRUE,
        NOW (),
        'https://example.com/images/map.jpg'
    );

-- =========================
-- IMAGES
-- =========================
INSERT INTO
    images (article_id, image_url, alt_text)
VALUES
    (
        1,
        'https://example.com/images/iran-energy.jpg',
        'Infrastructure énergétique Iran'
    ),
    (
        2,
        'https://example.com/images/missiles.jpg',
        'Missiles en lancement'
    ),
    (
        3,
        'https://example.com/images/g7.jpg',
        'Sommet du G7'
    ),
    (
        4,
        'https://example.com/images/diplomacy.jpg',
        'Discussion diplomatique'
    ),
    (
        5,
        'https://example.com/images/oil.jpg',
        'Marché du pétrole'
    ),
    (
        6,
        'https://example.com/images/map.jpg',
        'Carte du Moyen-Orient'
    );