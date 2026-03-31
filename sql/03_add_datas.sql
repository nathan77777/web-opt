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
        'Guerre en Iran : Escalade des frappes sur les infrastructures énergétiques',
        'escalade-frappes-infrastructures-iran-2026',
        'La guerre en Iran entre dans une nouvelle phase critique avec des frappes militaires ciblant des infrastructures énergétiques stratégiques iraniennes. Ces attaques dans le cadre de la guerre Iran-Israël paralysent une partie de la production pétrolière iranienne et provoquent une onde de choc sur les marchés internationaux de l''énergie. Les experts s''accordent à dire que cette escalade de la guerre en Iran pourrait durablement déstabiliser l''approvisionnement mondial en pétrole si aucune solution diplomatique n''est trouvée rapidement.',
        'Guerre en Iran : des frappes militaires ciblent les infrastructures énergétiques iraniennes, aggravant la crise et perturbant les marchés pétroliers mondiaux.',
        1,
        TRUE,
        NOW (),
        'iran-militaire-3.jpg'
    ),
    -- 2
    (
        1,
        'Guerre Iran-Israël : Une nouvelle vague de missiles iraniens lancés vers Israël',
        'missiles-iraniens-israel-2026',
        'Dans le cadre de la guerre Iran-Israël, l''Iran a lancé une nouvelle vague de missiles balistiques en direction du territoire israélien, touchant plusieurs zones urbaines et militaires. La guerre en Iran et ses répercussions directes sur Israël s''intensifient : les autorités israéliennes signalent de nombreux blessés ainsi que d''importants dégâts matériels. Cette offensive iranienne marque une nouvelle escalade dans la guerre qui oppose l''Iran à ses adversaires régionaux depuis plusieurs mois.',
        'Guerre Iran-Israël : l''Iran tire une nouvelle salve de missiles vers Israël, aggravant le conflit et provoquant des victimes et destructions.',
        1,
        TRUE,
        NOW (),
        'iran-militaire-1.jpg'
    ),
    -- 3
    (
        2,
        'Guerre en Iran : Le G7 appelle à la fin des attaques contre les civils',
        'g7-appel-fin-attaques-civils-iran',
        'Face à l''escalade de la guerre en Iran et à ses conséquences humanitaires dramatiques, les ministres des Affaires étrangères du G7 ont publié une déclaration conjointe exigeant un arrêt immédiat des attaques visant les populations civiles. La guerre en Iran préoccupe profondément la communauté internationale : le G7 appelle toutes les parties impliquées dans ce conflit iranien à respecter le droit international humanitaire et à privilégier la voie diplomatique pour mettre fin aux hostilités.',
        'Guerre en Iran : le G7 exige l''arrêt immédiat des attaques contre les civils et appelle à une solution diplomatique au conflit iranien.',
        1,
        TRUE,
        NOW (),
        'iran-diplomatie-2.jpg'
    ),
    -- 4
    (
        2,
        'Guerre en Iran : Négociations tendues entre les États-Unis et Téhéran',
        'negociations-usa-iran-2026',
        'Alors que la guerre en Iran continue de faire rage, des négociations diplomatiques discrètes sont en cours entre Washington et Téhéran pour tenter de stopper l''escalade militaire. Ces pourparlers autour de la guerre Iran-USA se déroulent dans un contexte de forte méfiance mutuelle : les progrès restent très incertains et les positions des deux parties demeurent éloignées. La communauté internationale surveille de près ces négociations, espérant qu''elles puissent ouvrir une voie vers la désescalade de la guerre en Iran.',
        'Guerre en Iran : des négociations diplomatiques difficiles sont engagées entre les États-Unis et l''Iran pour tenter de mettre fin au conflit.',
        1,
        TRUE,
        NOW (),
        'iran-diplomatie-1.jpeg'
    ),
    -- 5
    (
        3,
        'Guerre en Iran : L''impact économique du conflit sur les prix du pétrole',
        'impact-guerre-iran-prix-petrole',
        'La guerre en Iran produit des effets dévastateurs sur l''économie mondiale, en premier lieu sur les prix du pétrole qui ont bondi de manière spectaculaire sur les marchés internationaux. La guerre Iran-Israël et les frappes sur les sites de production pétroliers iraniens alimentent une incertitude majeure sur l''offre mondiale d''énergie. Les économistes avertissent que si la guerre en Iran venait à s''étendre, le choc pétrolier pourrait déclencher une récession dans plusieurs pays importateurs.',
        'Guerre en Iran : le conflit fait flamber les prix du pétrole et menace l''économie mondiale, avec un risque de choc énergétique majeur.',
        1,
        TRUE,
        NOW (),
        'iran-economie-1.jpeg'
    ),
    -- 6
    (
        4,
        'Guerre en Iran : Le risque d''une extension régionale du conflit au Moyen-Orient',
        'extension-regionale-conflit-iran',
        'La guerre en Iran inquiète de plus en plus les analystes géopolitiques qui redoutent un embrasement généralisé du Moyen-Orient. L''escalade de la guerre Iran-Israël et les tensions avec d''autres acteurs régionaux comme le Liban, la Syrie et l''Irak font craindre que le conflit ne dépasse largement les frontières iraniennes. Si la guerre en Iran venait à s''étendre à toute la région, les conséquences humanitaires, économiques et politiques seraient considérables pour la stabilité mondiale.',
        'Guerre en Iran : le risque d''extension régionale du conflit menace la stabilité du Moyen-Orient et préoccupe la communauté internationale.',
        1,
        TRUE,
        NOW (),
        'iran-economie-2.jpg'
    );

-- =========================
-- IMAGES
-- =========================
INSERT INTO
    images (article_id, image_url, alt_text)
VALUES
    (
        1,
        'iran-militaire-3.jpg',
        'Guerre en Iran - Infrastructure énergétique détruite'
    ),
    (
        2,
        'iran-militaire-1.jpg',
        'Guerre Iran-Israël - Missiles iraniens en lancement'
    ),
    (
        3,
        'iran-diplomatie-2.jpg',
        'Guerre en Iran - Réunion du G7 sur le conflit iranien'
    ),
    (
        4,
        'iran-diplomatie-1.jpg',
        'Guerre en Iran - Négociations diplomatiques USA-Iran'
    ),
    (
        5,
        'iran-economie-1.jpeg',
        'Guerre en Iran - Impact sur les prix du pétrole'
    ),
    (
        6,
        'iran-economie-2.jpg',
        'Guerre en Iran - Carte du Moyen-Orient et extension du conflit'
    );