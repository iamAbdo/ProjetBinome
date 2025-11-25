<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'binome_shop2';
const DB_USER = 'root';
const DB_PASS = '';

$connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($connection->connect_error) {
    throw new RuntimeException('Connexion MySQL échouée : ' . $connection->connect_error);
}

function sanitize(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function ensure_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}


