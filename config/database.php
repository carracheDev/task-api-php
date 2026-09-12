<?php

function getDbConnection(): PDO
{
    return new PDO(
        'pgsql:host=127.0.0.1;port=5432;dbname=taches_test',
        'postgres',
        'motdepasse123',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}