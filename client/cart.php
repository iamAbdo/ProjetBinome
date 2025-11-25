<?php
declare(strict_types=1);
$pageTitle = 'Panier';
require __DIR__ . '/includes/header.php';

$rawCart = $_COOKIE['cart_items'] ?? '[]';
$cartPayload = json_decode($rawCart, true);
$cartItems = [];
$normalizedCart = [];

if (is_array($cartPayload)) {
    foreach ($cartPayload as $entry) {
        $productId = (int)($entry['id'] ?? 0);
        $qty = max(1, (int)($entry['qty'] ?? 1));

        $normalizedCart[$productId] = $qty;
    }
}

if (!empty($normalizedCart)) {
    $productStmt = $connection->prepare('SELECT id, name, price, image FROM products WHERE id = ?');

    if ($productStmt) {
        foreach ($normalizedCart as $productId => $qty) {
            $productStmt->bind_param('i', $productId);
            $productStmt->execute();
            $product = $productStmt->get_result()?->fetch_assoc();

            if ($product) {
                $price = (float)$product['price'];

                $cartItems[] = [
                    'id' => (int)$product['id'],
                    'name' => $product['name'],
                    'price' => $price,
                    'image' => $product['image'] ?? '',
                    'qty' => $qty,
                    'line_total' => $qty * $price,
                ];
            }
        }

        $productStmt->close();
    }
}

$cartTotal = array_sum(array_column($cartItems, 'line_total'));
?>

<main class="cart-main" data-cart-page>
    <div class="section-title" style="margin-top: 2rem;">
        <div>
            <p style="color: var(--muted); text-transform: uppercase; letter-spacing: 0.2em; margin: 0;">Panier</p>
            <h2>Vos pièces sélectionnées</h2>
        </div>
        <a class="chip" href="products.php">Continuer vos achats</a>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="card" style="max-width: 760px; margin: 2rem auto; padding: 2rem;">
            <div class="card-body">
                <p class="empty-state">Votre panier est vide. Parcourez le catalogue pour ajouter des articles.</p>
                <a class="btn btn-primary" href="products.php" style="align-self: flex-start;">Voir les produits</a>
            </div>
        </div>
    <?php else: ?>
        <section class="cart-layout">
            <div class="cart-items card">
                <div class="cart-items-header">
                    <h3>Articles</h3>
                    <button class="link-btn" data-empty-cart type="button">Vider le panier</button>
                </div>
                <div class="cart-list">
                    <?php foreach ($cartItems as $item): ?>
                        <article class="cart-row">
                            <div class="cart-row-media">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?= sanitize($item['image']); ?>" alt="<?= sanitize($item['name']); ?>">
                                <?php else: ?>
                                    <div class="cart-row-placeholder">Image indisponible</div>
                                <?php endif; ?>
                            </div>
                            <div class="cart-row-content">
                                <div>
                                    <strong><?= sanitize($item['name']); ?></strong>
                                    <p style="margin: 0; color: var(--muted);"><?= number_format((float)$item['price'], 0, ',', ' '); ?> DA</p>
                                </div>
                                <div class="cart-row-actions">
                                    <label>
                                        Qté
                                        <input type="number" min="1" step="1" value="<?= (int)$item['qty']; ?>" data-item-qty="<?= (int)$item['id']; ?>">
                                    </label>
                                    <span class="line-total"><?= number_format((float)$item['line_total'], 0, ',', ' '); ?> DA</span>
                                    <button class="link-btn" type="button" data-remove-item="<?= (int)$item['id']; ?>">Supprimer</button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="cart-summary">
                    <span>Total estimé</span>
                    <strong><?= number_format((float)$cartTotal, 0, ',', ' '); ?> DA</strong>
                </div>
            </div>
            <div class="card checkout-card">
                <h3>Finaliser la réservation</h3>
                <p style="color: var(--muted); margin-top: -0.25rem;">Indiquez vos coordonnées, nous vous recontactons pour confirmer.</p>
                <div id="order-status"></div>
                <form id="order-form" method="post" action="order-handler.php">
                    <label>
                        Nom complet
                        <input type="text" name="client_name" required placeholder="Ex. Lina Bensalem">
                    </label>
                    <label>
                        Téléphone
                        <input type="tel" name="client_phone" required placeholder="+213 5 55 55 55 55">
                    </label>
                    <label>
                        Adresse complète
                        <textarea name="client_address" rows="3" required placeholder="Numéro, rue, commune, wilaya"></textarea>
                    </label>
                    <label>
                        Email
                        <input type="email" name="client_email" placeholder="vous@email.fr">
                    </label>
                    <button class="btn btn-primary" type="submit">Envoyer la commande</button>
                </form>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

