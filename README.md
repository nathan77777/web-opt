# Projet PHP natif + PostgreSQL (frontoffice / backoffice)

Mini projet sans dependance externe (framework) avec:

- frontoffice public (liste des articles publies)
- backoffice avec authentification
- CRUD article partiel (create + read + update)
- upload d'images d'articles (JPG/PNG/GIF/WEBP)
- stack Docker PHP Apache + PostgreSQL

## Fonctionnalites disponibles

### Frontoffice

- liste des articles actifs et publies
- affichage du titre, slug, categorie, meta description

### Backoffice

- connexion/deconnexion administrateur
- liste des articles (tous statuts)
- page detail d'un article
- creation d'article
- edition d'article
- upload multiple d'images avec choix d'une image principale

## Routes principales

- `/` -> redirection vers `/frontoffice/`
- `/frontoffice/` -> liste publique des articles
- `/backoffice/` -> page de connexion
- `/backoffice/login.php` -> traitement POST du login
- `/backoffice/articles_list.php` -> liste backoffice (protegee)
- `/backoffice/article_details.php?id={id}` -> detail article (protegee)
- `/backoffice/form/articles_create.php` -> creation/edition (protegee)
- `/backoffice/form/articles_create_process.php` -> traitement POST creation/edition (protegee)
- `/backoffice/logout.php` -> deconnexion

## Structure actuelle (fichiers cles)

- `public/index.php` : redirection vers `/frontoffice/`
- `public/frontoffice/index.php` : affichage frontoffice
- `public/backoffice/index.php` : formulaire de connexion
- `public/backoffice/login.php` : traitement login
- `public/backoffice/articles_list.php` : liste des articles backoffice
- `public/backoffice/article_details.php` : page detail article
- `public/backoffice/form/articles_create.php` : formulaire create/edit article
- `public/backoffice/form/articles_create_process.php` : validation + persistance + upload
- `public/backoffice/logout.php` : deconnexion
- `src/database.php` : connexions PostgreSQL (`pg_*` et `PDO`)
- `src/auth.php` : logique d'authentification (login / guard / logout)
- `src/articles.php` : requetes/metiers articles + images
- `sql/01_init_tables.sql` : schema + donnees de demo
- `docker/php/Dockerfile` : image PHP Apache avec extension pgsql
- `docker/php/vhost.conf` : configuration Apache
- `docker-compose.yml` : stack app + PostgreSQL + volumes

## Prerequis (execution locale sans Docker)

- PHP 8+
- Extension PostgreSQL pour PHP (`pgsql` + `pdo_pgsql` recommande)
- PostgreSQL

## 1) Initialiser la base

Exemple avec `psql`:

```bash
createdb webopt
psql -d webopt -f sql/01_init_tables.sql
```

Le script SQL cree:

- users
- categories
- articles
- images
- un compte admin de demo
- des categories et articles de demo

Compte de test:

- email: `admin@example.com`
- mot de passe: `admin123`

## 2) Variables de connexion DB (optionnel)

Par defaut, le projet utilise:

- DB_HOST=127.0.0.1
- DB_PORT=5432
- DB_NAME=webopt
- DB_USER=postgres
- DB_PASS=nathan

Tu peux les surcharger avant de lancer le serveur:

```bash
export DB_HOST=127.0.0.1
export DB_PORT=5432
export DB_NAME=webopt
export DB_USER=postgres
export DB_PASS=nathan
```

## 3) Lancer le serveur PHP

```bash
php -S localhost:8000 -t public
```

Puis ouvre:

- Frontoffice: http://localhost:8000/frontoffice/
- Backoffice (login): http://localhost:8000/backoffice/

## Lancer avec Docker

Construire et demarrer:

```bash
docker compose up --build
```

Puis ouvrir:

- Application: http://localhost:9000
- Frontoffice: http://localhost:9000/frontoffice/
- Backoffice (login): http://localhost:9000/backoffice/

Ports exposes:

- App PHP/Apache: `9000` (hote) -> `80` (conteneur)
- PostgreSQL: `5433` (hote) -> `5432` (conteneur)

Volumes Docker:

- `db_data` : persistance des donnees PostgreSQL
- `uploads_data` : persistance des images uploades (`/uploads`)

Arreter les conteneurs:

```bash
docker compose down
```

Reinitialiser completement la base (et rejouer `sql/01_init_tables.sql`):

```bash
docker compose down -v
docker compose up --build
```

## Limites connues / suite possible

- frontoffice detail article non implemente
- suppression d'article non implementee
- styles backoffice create/edit encore basiques
