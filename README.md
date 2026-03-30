# Projet PHP natif + PostgreSQL (login)

Mini projet sans dependances externes:

- page de login
- traitement du formulaire
- redirection en cas de succes
- protection de la page connectee

## Arborescence

- `public/index.php` : formulaire de connexion
- `public/login.php` : traitement POST login
- `public/dashboard.php` : page protegee
- `public/logout.php` : deconnexion
- `config/database.php` : connexion PostgreSQL
- `config/auth.php` : logique d'authentification
- `sql/01_login.sql` : script SQL login (utilise par Docker)
- `sql/script.sql` : script SQL metier existant (optionnel)
- `Dockerfile` : image PHP avec extension PostgreSQL
- `docker-compose.yml` : stack app + PostgreSQL

## Prerequis

- PHP 8+
- Extension PostgreSQL pour PHP (`pgsql`)
- PostgreSQL

## 1) Initialiser la base

Exemple avec `psql`:

```bash
createdb webopt
psql -d webopt -f sql/01_login.sql
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

- http://localhost:8000

Compte de test:

- email: `admin@example.com`
- mot de passe: `admin123`

## Lancer avec Docker

Construire et demarrer:

```bash
docker compose up --build
```

Puis ouvrir:

- http://localhost:8000

Arreter les conteneurs:

```bash
docker compose down
```

Si tu veux reinitialiser la base (et rejouer `sql/01_login.sql`):

```bash
docker compose down -v
docker compose up --build
```
