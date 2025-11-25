<?php
declare(strict_types=1);
$pageTitle = 'Catégories';
$activePage = 'categories';
require __DIR__ . '/includes/header.php';

$message = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // crud
    $intent = $_POST['intent'] ?? '';
    $name = trim($_POST['name'] ?? '');

    try {
        // cas create
        if ($intent === 'create' && $name !== '') {
            $stmt = $connection->prepare('INSERT INTO categories (name) VALUES (?)');

            if (!$stmt) {
                throw new RuntimeException('Erreur préparation SQL : ' . $connection->error);
            }

            $stmt->bind_param('s', $name);
            $stmt->execute();
            $message = 'Catégorie ajoutée.';
        } elseif ($intent === 'update' && $name !== '') {
            $stmt = $connection->prepare('UPDATE categories SET name = ? WHERE id = ?');

            if (!$stmt) {
                throw new RuntimeException('Erreur préparation SQL : ' . $connection->error);
            }

            $id = (int)$_POST['category_id'];
            $stmt->bind_param('si', $name, $id);
            $stmt->execute();
            $message = 'Catégorie mise à jour.';
        } elseif ($intent === 'delete') {
            $stmt = $connection->prepare('DELETE FROM categories WHERE id = ?');

            if (!$stmt) {
                throw new RuntimeException('Erreur préparation SQL : ' . $connection->error);
            }

            $id = (int)$_POST['category_id'];
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $message = 'Catégorie supprimée.';
        } else {
            $isError = true;
            $message = 'Merci de renseigner un nom.';
        }
    } catch (Throwable $exception) {
        $isError = true;
        $message = 'Action impossible sur cette catégorie.';
    }
}

// jib cat li nmodifiwiwah
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $connection->prepare('SELECT * FROM categories WHERE id = ?');

    if ($stmt) {
        $id = (int)$_GET['edit'];
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $editing = $stmt->get_result()?->fetch_assoc();
    } else {
        $editing = null;
    }
}

$res = $connection->query('SELECT * FROM categories ORDER BY name ASC');
$categories = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>

<section class="dashboard">
    <h1>Catégories</h1>
    <p style="color: var(--muted);">Organisez vos produits par univers.</p>

    <?php if ($message): ?>
        <div class="alert <?= $isError ? 'alert-error' : 'alert-success'; ?>"><?= sanitize($message); ?></div>
    <?php endif; ?>

    <div class="table-card" style="margin-bottom: 2rem; padding: 2rem;">
        <h2><?= $editing ? 'Modifier' : 'Ajouter'; ?> une catégorie</h2>
        <form method="post" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="intent" value="<?= $editing ? 'update' : 'create'; ?>">
            <?php if ($editing): ?>
                <input type="hidden" name="category_id" value="<?= (int)$editing['id']; ?>">
            <?php endif; ?>
            <label style="flex: 1; min-width: 240px;">
                Nom
                <input type="text" name="name" required value="<?= sanitize($editing['name'] ?? ''); ?>">
            </label>
            <button class="btn btn-primary" type="submit"><?= $editing ? 'Mettre à jour' : 'Ajouter'; ?></button>
            <?php if ($editing): ?>
                <a class="btn" href="categories.php">Annuler</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($categories)): ?>
                <tr><td colspan="2" class="empty-state">Aucune catégorie créée.</td></tr>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= sanitize($category['name']); ?></td>
                        <td style="text-align: right;">
                            <a class="btn" href="categories.php?edit=<?= (int)$category['id']; ?>">Modifier</a>
                            <form method="post" style="display: inline;" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                <input type="hidden" name="intent" value="delete">
                                <input type="hidden" name="category_id" value="<?= (int)$category['id']; ?>">
                                <button class="btn" type="submit" style="background: rgba(248,113,113,0.15); color: #f87171;">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

