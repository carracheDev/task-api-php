<?php

function getDbConnection(): PDO
{
    $databaseUrl = getenv('DB_URL');

    if ($databaseUrl) {
        $parts = parse_url($databaseUrl);
        parse_str($parts['query'] ?? '', $query);

        $host = $parts['host'] ?? '127.0.0.1';
        $port = $parts['port'] ?? 5432;
        $database = ltrim($parts['path'] ?? '/taches_test', '/');
        $username = rawurldecode($parts['user'] ?? 'postgres');
        $password = rawurldecode($parts['pass'] ?? '');
        $sslmode = $query['sslmode'] ?? 'require';
    } else {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '5432';
        $database = getenv('DB_DATABASE') ?: 'taches_test';
        $username = getenv('DB_USERNAME') ?: 'postgres';
        $password = getenv('DB_PASSWORD') ?: 'motdepasse123';
        $sslmode = getenv('DB_SSLMODE') ?: 'prefer';
    }

    return new PDO(
        "pgsql:host={$host};port={$port};dbname={$database};sslmode={$sslmode}",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}