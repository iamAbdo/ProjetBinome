<?php
declare(strict_types=1);
$pageTitle = 'Lorem Ipsum — Accueil';
require __DIR__ . '/includes/header.php';

$cRes = $connection->query('SELECT id, name FROM categories ORDER BY name ASC LIMIT 6');
$categories = $cRes ? $cRes->fetch_all(MYSQLI_ASSOC) : [];
$nRes = $connection->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC LIMIT 3');
$nouveautes = $nRes ? $nRes->fetch_all(MYSQLI_ASSOC) : [];
?>

<main>
    <section class="hero">
        <div>
            <h1>Lorem ipsum dolor sit amet.</h1>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.</p>
            <div class="chip-group" style="margin: 2rem 0;">
                <?php foreach ($categories as $cat): ?>
                    <span class="chip"><?= sanitize($cat['name']); ?></span>
                <?php endforeach; ?>
            </div>
            <a class="btn btn-primary" href="products.php">Voir les produits</a>
        </div>
        <div class="hero-card">
            <h3>Exemples</h3>
            <p style="color: var(--muted); margin-bottom: 1.5rem;">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            <?php foreach ($nouveautes as $product): ?>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; gap: 1rem;">
                    <div>
                        <strong><?= sanitize($product['name']); ?></strong>
                        <p style="margin: 0; color: var(--muted);"><?= sanitize($product['category_name'] ?? 'Collection Exemple'); ?></p>
                    </div>
                    <span class="price"><?= number_format((float)$product['price'], 0, ',', ' '); ?> DA</span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="section-title">
        <h2>Pourquoi Nous ?</h2>
    </div>
    <section class="grid">
        <article class="card">
            <div class="card-body">
                <h3>Lorem Ipsum</h3>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean commodo ligula eget dolor.</p>
            </div>
        </article>
        <article class="card">
            <div class="card-body">
                <h3>Dolor Sit</h3>
                <p>Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus.</p>
            </div>
        </article>
        <article class="card">
            <div class="card-body">
                <h3>Consectetur</h3>
                <p>Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui.</p>
            </div>
        </article>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

