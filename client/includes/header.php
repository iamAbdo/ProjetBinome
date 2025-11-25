<?php
declare(strict_types=1);
require_once __DIR__ . '/../../private/config.php';

$pageTitle = $pageTitle ?? 'site (exemple)';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= sanitize($pageTitle); ?></title>
    <link rel="stylesheet" href="../public/css/style.css">
    <script defer src="../public/js/app.js"></script>
</head>
<body>
<div class="layout">
    <header class="site-header">
        <nav>
            <a class="brand" href="index.php">Site monome</a>
            <div class="nav-links">
                <a href="index.php">Accueil</a>
                <a href="products.php">Produits</a>
                <a href="contact.php">Contact</a>
                <a href="apropos.html">À propos</a>
                <a href="cart.php" class="cart-link">Panier (<span data-cart-count>0</span>)</a>
            </div>
            <a class="btn btn-primary" href="../admin/login.php">Admin</a>
        </nav>
    </header>


