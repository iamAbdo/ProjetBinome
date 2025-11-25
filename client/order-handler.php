<?php
declare(strict_types=1);
require __DIR__ . '/../private/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Méthode non autorisée.']);
    exit;
}

// gather info
$clientName = trim($_POST['client_name'] ?? '');
$clientPhone = trim($_POST['client_phone'] ?? '');
$clientAddress = trim($_POST['client_address'] ?? '');
$clientEmail = trim($_POST['client_email'] ?? '');
$rawCart = $_COOKIE['cart_items'] ?? '[]';
$cartPayload = json_decode($rawCart, true);
$normalizedCart = [];

if (is_array($cartPayload)) {
    foreach ($cartPayload as $entry) {
        $productId = (int) ($entry['id'] ?? 0);
        $qty = max(1, (int) ($entry['qty'] ?? 1));

        $normalizedCart[$productId] = $qty;

    }
}

// verify info
if ($clientName === '' || $clientPhone === '' || $clientAddress === '' || empty($normalizedCart)) {
    http_response_code(422);
    echo json_encode(['message' => 'Merci de compléter les informations et d’ajouter au moins un produit.']);
    exit;
}

$connection->begin_transaction();

try {
    $orderStmt = $connection->prepare('INSERT INTO orders (client_name, client_phone, client_address, client_email) VALUES (?, ?, ?, ?)');
    $orderStmt->bind_param('ssss', $clientName, $clientPhone, $clientAddress, $clientEmail);
    $orderStmt->execute();

    $orderId = $connection->insert_id;


    $productStmt = $connection->prepare('SELECT id, price FROM products WHERE id = ?');
    $itemStmt = $connection->prepare('INSERT INTO order_items (order_id, product_id, qty, unit_price) VALUES (?, ?, ?, ?)');

    foreach ($normalizedCart as $productId => $qty) {
        $productStmt->bind_param('i', $productId);
        $productStmt->execute();
        $product = $productStmt->get_result()?->fetch_assoc();

        if (!$product) {
            throw new RuntimeException('Produit introuvable : ' . $productId);
        }

        $unitPrice = (float) $product['price'];

        $itemStmt->bind_param('iiid', $orderId, $productId, $qty, $unitPrice);

        if (!$itemStmt->execute()) {
            throw new RuntimeException('Insertion article échouée : ' . $itemStmt->error);
        }
    }

    $connection->commit();
    // supprimer el painer
    setcookie('cart_items', '', time() - 3600, '/');

    $response = [
        'message' => 'Merci ' . $clientName . ', votre commande est enregistrée !',
        'order_id' => $orderId
    ];

    echo json_encode($response);
} catch (Throwable $exception) {
    $connection->rollback();
    http_response_code(500);
    echo json_encode(['message' => 'Impossible d’enregistrer la commande.']);
} finally {
    if (isset($orderStmt) && $orderStmt instanceof mysqli_stmt) {
        $orderStmt->close();
    }
    if (isset($productStmt) && $productStmt instanceof mysqli_stmt) {
        $productStmt->close();
    }
    if (isset($itemStmt) && $itemStmt instanceof mysqli_stmt) {
        $itemStmt->close();
    }
}

