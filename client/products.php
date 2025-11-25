<?php
declare(strict_types=1);
$pageTitle = 'Template — Catalogue';
require __DIR__ . '/includes/header.php';

$res = $connection->query("SELECT id, name, LOWER(REPLACE(name, ' ', '-')) AS slug FROM categories ORDER BY name ASC");
$categories = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

$pRes = $connection->query("SELECT p.*, c.name AS category_name, LOWER(REPLACE(IFNULL(c.name, 'collection'), ' ', '-')) AS category_slug FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC");
$products = $pRes ? $pRes->fetch_all(MYSQLI_ASSOC) : [];
?>

<main>
    <div class="section-title">
        <div>
            <p style="color: var(--muted); text-transform: uppercase; letter-spacing: 0.2em; margin: 0;">Catalogue</p>
            <h2>Nos pièces signatures</h2>
        </div>
        <p style="max-width: 360px; color: var(--muted);">Cliquez sur « Commander » pour pré-réserver l’article. Aucun paiement en ligne, nous vous rappelons pour finaliser.</p>
    </div>

    <div class="section-title" style="margin-top: 0;">
        <div class="chip-group">
            <span class="chip active" data-filter="all">Tous</span>
            <?php foreach ($categories as $cat): ?>
                <span class="chip" data-filter="<?= sanitize($cat['slug']); ?>"><?= sanitize($cat['name']); ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <section class="grid">
        <?php if (empty($products)): ?>
            <div class="card" style="grid-column: 1 / -1;">
                <div class="card-body empty-state">
                    Aucun produit n’a encore été ajouté. Revenez bientôt.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <article class="card" data-category="<?= sanitize($product['category_slug']); ?>">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?= sanitize($product['image']); ?>" alt="<?= sanitize($product['name']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <p style="color: var(--muted); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.2em;">
                            <?= sanitize($product['category_name'] ?? 'Collection Template'); ?>
                        </p>
                        <h3><?= sanitize($product['name']); ?></h3>
                        <p style="color: var(--muted);"><?= sanitize($product['description'] ?? 'Pièce iconique imaginée pour une expérience sensuelle et durable.'); ?></p>
                        <div class="price"><?= number_format((float)$product['price'], 0, ',', ' '); ?> DA</div>
                        <button
                            class="btn btn-primary"
                            type="button"
                            data-add-cart
                            data-id="<?= (int)$product['id']; ?>"
                            data-name="<?= sanitize($product['name']); ?>"
                            data-price="<?= (float)$product['price']; ?>"
                            data-image="<?= sanitize($product['image'] ?? ''); ?>"
                        >
                            Ajouter au panier
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>