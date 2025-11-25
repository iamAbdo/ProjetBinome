<?php
declare(strict_types=1);
require __DIR__ . '/../../private/config.php';

ensure_session();

function is_admin_authenticated(): bool
{
    return isset($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin_authenticated()) {
        header('Location: login.php');
        exit;
    }
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

