<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_admin();

$pageTitle = $pageTitle ?? 'Administration Template';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= sanitize($pageTitle); ?></title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<div class="admin-layout">
    <aside class="sidebar">
        <div class="brand" style="font-size: 1.1rem;">Template Admin</div>
        <a href="products.php" class="<?= $activePage === 'products' ? 'active' : ''; ?>">Produits</a>
        <a href="categories.php" class="<?= $activePage === 'categories' ? 'active' : ''; ?>">Catégories</a>
        <a href="orders.php" class="<?= $activePage === 'orders' ? 'active' : ''; ?>">Commandes</a>
        <a href="admins.php" class="<?= $activePage === 'admins' ? 'active' : ''; ?>">Administrateurs</a>
        <a href="logout.php" style="margin-top: auto;">Se déconnecter</a>
    </aside>
    <main class="admin-content">

