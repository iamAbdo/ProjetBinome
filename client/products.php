<?php
declare(strict_types=1);
$pageTitle = 'Template — Catalogue';
require __DIR__ . '/includes/header.php';

// jib les cat
$res = $connection->query("SELECT id, name, LOWER(REPLACE(name, ' ', '-')) AS slug FROM categories ORDER BY name ASC");
$categories = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

// pagination + filtre par catégorie
$perPage = 4;
$page = max(1, (int) ($_GET['page'] ?? 1)); //min 1
$categoryId = isset($_GET['categorie']) ? (int) $_GET['categorie'] : 0;

// wach nzido fl where
$where = '';
$params = [];
// ida kayn cat filter
if ($categoryId > 0) {
    $where = 'WHERE p.category_id = ' . $categoryId;
}

// total pour pagination (jib total bach n3rfo ch7al kayn)
$countSql = "SELECT COUNT(*) AS total FROM products p " . ($categoryId > 0 ? "WHERE p.category_id = $categoryId" : "");
$countRes = $connection->query($countSql);
$total = 0;
if ($countRes) {
    $r = $countRes->fetch_assoc();
    $total = (int) ($r['total'] ?? 0);
}

$totalPages = (int) ceil($total / $perPage);
$page = min($page, max(1, $totalPages));
$offset = ($page - 1) * $perPage;

$pSql = "SELECT p.*, c.name AS category_name, LOWER(REPLACE(IFNULL(c.name, 'collection'), ' ', '-')) AS category_slug FROM products p LEFT JOIN categories c ON c.id = p.category_id "
    . ($where ? ($where . ' ') : '')
    . "ORDER BY p.created_at DESC LIMIT $offset, $perPage";
$pRes = $connection->query($pSql);
$products = $pRes ? $pRes->fetch_all(MYSQLI_ASSOC) : [];
?>

<main>
    <div class="section-title">
        <div>
            <p style="color: var(--muted); text-transform: uppercase; letter-spacing: 0.2em; margin: 0;">Catalogue</p>
            <h2>Nos pièces signatures</h2>
        </div>
        <p style="max-width: 360px; color: var(--muted);">Cliquez sur « Commander » pour pré-réserver l’article. Aucun
            paiement en ligne, nous vous rappelons pour finaliser.</p>
    </div>

    <div class="section-title" style="margin-top: 0;">
        <div class="chip-group">
            <a class="chip <?= !$categoryId ? 'active' : ''; ?>" href="products.php?page=1">Tous</a>
            <?php foreach ($categories as $cat): ?>
                <?php $isActive = $categoryId !== null && (int) $cat['id'] === (int) $categoryId; ?>
                <a class="chip <?= $isActive ? 'active' : ''; ?>"
                    href="products.php?categorie=<?= (int) $cat['id']; ?>&amp;page=1"><?= sanitize($cat['name']); ?></a>
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
                        <p style="color: var(--muted);">
                            <?= sanitize($product['description'] ?? 'Pièce iconique imaginée pour une expérience sensuelle et durable.'); ?>
                        </p>
                        <div class="price"><?= number_format((float) $product['price'], 0, ',', ' '); ?> DA</div>
                        <button class="btn btn-primary" type="button" data-add-cart data-id="<?= (int) $product['id']; ?>"
                            data-name="<?= sanitize($product['name']); ?>" data-price="<?= (float) $product['price']; ?>"
                            data-image="<?= sanitize($product['image'] ?? ''); ?>">
                            Ajouter au panier
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Pagination"
            style="display:flex; gap:0.5rem; justify-content:center; margin: 2rem 0;">
            <?php
            $baseUrl = basename($_SERVER['PHP_SELF']);
            $queryCategory = $categoryId > 0 ? '&categorie=' . $categoryId : '';

            $prev = max(1, $page - 1);
            $next = min($totalPages, $page + 1);
            ?>

            <?php if ($page > 1): ?>
                <a class="btn" href="<?= sanitize($baseUrl); ?>?page=<?= $prev . $queryCategory; ?>">&laquo; Précédent</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a class="btn<?= $i === $page ? ' btn-primary' : ''; ?>"
                    href="<?= sanitize($baseUrl); ?>?page=<?= $i . $queryCategory; ?>"><?= $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a class="btn" href="<?= sanitize($baseUrl); ?>?page=<?= $next . $queryCategory; ?>">Suivant &raquo;</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>