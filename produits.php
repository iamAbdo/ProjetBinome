<?php
// preserve query string when redirecting so URLs like
// produits.php?categorie=2&page=3 keep parameters
$qs = $_SERVER['QUERY_STRING'] ?? '';
header('Location: client/products.php' . ($qs ? '?' . $qs : ''));
exit;

