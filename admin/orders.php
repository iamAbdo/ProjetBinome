<?php
declare(strict_types=1);
$pageTitle = 'Commandes';
$activePage = 'orders';
require __DIR__ . '/includes/header.php';

$res = $connection->query(
    'SELECT o.*, '
    . 'COALESCE(GROUP_CONCAT(CONCAT(p.name, " x", oi.qty) SEPARATOR ", "), "—") AS items '
    . 'FROM orders o '
    . 'LEFT JOIN order_items oi ON oi.order_id = o.id '
    . 'LEFT JOIN products p ON p.id = oi.product_id '
    . 'GROUP BY o.id '
    . 'ORDER BY o.created_at DESC'
);

$orders = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>

<section class="dashboard">
    <h1>Commandes</h1>
    <p style="color: var(--muted);">Suivi des demandes reçues via le portail client.</p>

    <div class="table-card">
        <table>
            <thead>
            <tr>
                <th>Client</th>
                <th>Produits</th>
                <th>Contact</th>
                <th>Reçue le</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="4" class="empty-state">Aucune commande pour l’instant.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <strong><?= sanitize($order['client_name']); ?></strong><br>
                            <?= sanitize($order['client_address'] ?? ''); ?>
                        </td>
                        <td><?= sanitize($order['items'] ?? '—'); ?></td>
                        <td>
                            <?= sanitize($order['client_phone']); ?><br>
                            <?= sanitize($order['client_email'] ?? ''); ?>
                        </td>
                        <td><?= sanitize(date('d/m/Y H:i', strtotime($order['created_at']))); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

