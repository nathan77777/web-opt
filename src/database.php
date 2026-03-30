<?php

declare(strict_types=1);

function db_connect()
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '5432';
    $name = getenv('DB_NAME') ?: 'webopt';
    $user = getenv('DB_USER') ?: 'postgres';
    $pass = getenv('DB_PASS') ?: 'nathan';

    $connectionString = sprintf(
        'host=%s port=%s dbname=%s user=%s password=%s',
        $host,
        $port,
        $name,
        $user,
        $pass
    );

    $connection = pg_connect($connectionString);

    if ($connection === false) {
        http_response_code(500);
        exit('Erreur de connexion a la base de donnees.');
    }

    return $connection;
}

function getConnection(): PDO
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '5432';
    $name = getenv('DB_NAME') ?: 'webopt';
    $user = getenv('DB_USER') ?: 'postgres';
    $pass = getenv('DB_PASS') ?: 'nathan';

    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $name);

    try {
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $exception) {
        http_response_code(500);
        exit('Erreur de connexion a la base de donnees.');
    }
}
