<?php

function getDbConnection(): PDO
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '5432';
    $database = getenv('DB_DATABASE') ?: 'taches_test';
    $username = getenv('DB_USERNAME') ?: 'postgres';
    $password = getenv('DB_PASSWORD') ?: 'motdepasse123';
    $sslmode = getenv('DB_SSLMODE') ?: 'prefer';

    return new PDO(
        "pgsql:host={$host};port={$port};dbname={$database};sslmode={$sslmode}",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}