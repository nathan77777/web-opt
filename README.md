# Projet PHP natif + PostgreSQL (frontoffice / backoffice)

Mini projet sans dependances externes avec:

- frontoffice public (liste des articles publies)
- backoffice avec authentification
- page protegee (liste des articles)
- stack Docker PHP + PostgreSQL

## Structure actuelle

- `public/index.php` : redirection vers `/frontoffice/`
- `public/frontoffice/index.php` : affichage frontoffice
- `public/backoffice/index.php` : formulaire de connexion
- `public/backoffice/login.php` : traitement POST du login
- `public/backoffice/articles.php` : page protegee
- `public/backoffice/logout.php` : deconnexion
- `src/database.php` : connexion PostgreSQL
- `src/auth.php` : logique d'authentification (login / guard / logout)
- `src/articles.php` : requetes articles frontoffice/backoffice
- `sql/01_init_tables.sql` : creation tables + donnees de demo
- `docker/php/Dockerfile` : image PHP Apache avec extension pgsql
- `docker/php/vhost.conf` : configuration Apache
- `docker-compose.yml` : stack app + PostgreSQL

## Prerequis (execution locale sans Docker)

- PHP 8+
- Extension PostgreSQL pour PHP (`pgsql`)
- PostgreSQL

## 1) Initialiser la base

Exemple avec `psql`:

```bash
createdb webopt
psql -d webopt -f sql/01_init_tables.sql
```

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

Compte de test:

- email: `admin@example.com`
- mot de passe: `admin123`

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

Arreter les conteneurs:

```bash
docker compose down
```

Si tu veux reinitialiser la base (et rejouer `sql/01_init_tables.sql`):

```bash
docker compose down -v
docker compose up --build
```
