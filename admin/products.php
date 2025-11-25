<?php
declare(strict_types=1);
$pageTitle = 'Produits';
$activePage = 'products';
require __DIR__ . '/includes/header.php';

$result = $connection->query('SELECT id, name FROM categories ORDER BY name ASC');
$categories = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$message = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $intent = $_POST['intent'] ?? '';

    try {
        // Prepare upload directory (akdm dossier ida makach)
        $uploadBaseDir = __DIR__ . '/../public/images/products';
        if (!is_dir($uploadBaseDir)) {
            mkdir($uploadBaseDir, 0755, true);
        }

        // store uploaded file (image ta3 produit)
        $imagePath = null;
        if (!empty($_FILES['image_file']) && ($_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE)) {
            $file = $_FILES['image_file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Erreur lors de l\'envoi du fichier.');
            }

            // Limit size ( max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new RuntimeException('Le fichier est trop volumineux (max 5MB).');
            }

            // verifier type d'image
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            if (!isset($allowed[$mime])) {
                throw new RuntimeException('Type d\'image non supporté.');
            }

            // sauvgarder
            $ext = $allowed[$mime];
            $basename = bin2hex(random_bytes(8)) . '.' . $ext;
            $target = $uploadBaseDir . DIRECTORY_SEPARATOR . $basename;
            if (!move_uploaded_file($file['tmp_name'], $target)) {
                throw new RuntimeException('Impossible de sauvegarder le fichier image.');
            }

            // Public path saved in DB
            $imagePath = 'public/images/products/' . $basename;
        }

        // Cas de creation
        if ($intent === 'create') {
            $pName = trim($_POST['name']);
            $pPrice = (float) $_POST['price'];
            $pCategory = $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
            $pImage = "../" . $imagePath ?? null;
            $pDescription = trim($_POST['description']);

            $stmt = $connection->prepare('INSERT INTO products (name, price, category_id, image, description) VALUES (?, ?, ?, ?, ?)');

            if (!$stmt) {
                throw new RuntimeException('Erreur préparation SQL : ' . $connection->error);
            }

            $stmt->bind_param('sdiss', $pName, $pPrice, $pCategory, $pImage, $pDescription);
            $stmt->execute();
            $message = 'Produit ajouté avec succès.';

            // cas de modification
        } elseif ($intent === 'update') {

            // twsira 9dima
            $existingStmt = $connection->prepare('SELECT image FROM products WHERE id = ?');

            if (!$existingStmt) {
                throw new RuntimeException('Erreur préparation SQL : ' . $connection->error);
            }

            $pid = (int) $_POST['product_id'];
            $existingStmt->bind_param('i', $pid);
            $existingStmt->execute();
            $existing = $existingStmt->get_result()?->fetch_assoc();
            $existingImage = $existing['image'] ?? null;

            if ($imagePath === null) { // ida ma b3tch kheli 9dima
                $imagePath = $existingImage;
            } else {
                // fasi 9dima ila b3tna jdida
                if (!empty($existingImage)) {

                    // Remove ../ 
                    $clean = preg_replace('#^(\.\./)+#', '', $existingImage);

                    // path to image
                    $oldPath = realpath(__DIR__ . '/../' . $clean);

                    $realBase = realpath($uploadBaseDir);

                    if ($oldPath && $realBase && strpos($oldPath, $realBase) === 0) {
                        unlink($oldPath);
                    } else {
                        var_dump("FAILED", $oldPath, $realBase);
                    }
                }

            }

            $uName = trim($_POST['name']);
            $uPrice = (float) $_POST['price'];
            $uCategory = $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
            $uImage = $imagePath ?? null;
            $uDescription = trim($_POST['description']);
            $uId = (int) $_POST['product_id'];

            $stmt = $connection->prepare('UPDATE products SET name=?, price=?, category_id=?, image=?, description=? WHERE id=?');

            if (!$stmt) {
                throw new RuntimeException('Erreur préparation SQL : ' . $connection->error);
            }

            $stmt->bind_param('sdissi', $uName, $uPrice, $uCategory, $uImage, $uDescription, $uId);
            $stmt->execute();
            $message = 'Produit mis à jour.';

            // cas de suppression
        } elseif ($intent === 'delete') {
            // jib tswira bach nfasoha
            $delSel = $connection->prepare('SELECT image FROM products WHERE id = ?');

            if (!$delSel) {
                throw new RuntimeException('Erreur préparation SQL : ' . $connection->error);
            }

            $delId = (int) $_POST['product_id'];
            $delSel->bind_param('i', $delId);
            $delSel->execute();
            $toDel = $delSel->get_result()?->fetch_assoc();
            $toDelImage = $toDel['image'] ?? null;

            // fasi twsira
            if (!empty($toDelImage)) {

                // Remove ../ 
                $clean = preg_replace('#^(\.\./)+#', '', $toDelImage);

                // path to image
                $oldPath = realpath(__DIR__ . '/../' . $clean);

                $realBase = realpath($uploadBaseDir);

                if ($oldPath && $realBase && strpos($oldPath, $realBase) === 0) {
                    unlink($oldPath);
                } else {
                    var_dump("FAILED", $oldPath, $realBase);
                }
            }


            $delStmt = $connection->prepare('DELETE FROM products WHERE id = ?');

            if (!$delStmt) {
                throw new RuntimeException('Erreur préparation SQL : ' . $connection->error);
            }

            $delStmt->bind_param('i', $delId);
            $delStmt->execute();
            $message = 'Produit supprimé.';
        }


    } catch (Throwable $exception) {
        $isError = true;
        $message = 'Impossible de sauvegarder ce produit.';
    }
}

// jib les infos ta3 produit bach nmodifiwah
$editing = null;
if (isset($_GET['edit'])) {
    $editStmt = $connection->prepare('SELECT * FROM products WHERE id = ?');

    if ($editStmt) {
        $eid = (int) $_GET['edit'];
        $editStmt->bind_param('i', $eid);
        $editStmt->execute();
        $editing = $editStmt->get_result()?->fetch_assoc();
    } else {
        $editing = null;
    }
}


// jib kaml les prods
$res = $connection->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC');
$products = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>

<section class="dashboard">
    <h1>Catalogue produits</h1>
    <p style="color: var(--muted);">Ajoutez, modifiez ou supprimez vos références.</p>

    <?php if ($message): ?>
        <div class="alert <?= $isError ? 'alert-error' : 'alert-success'; ?>"><?= sanitize($message); ?></div>
    <?php endif; ?>

    <div class="table-card" style="margin-bottom: 2rem; padding: 2rem;">
        <h2><?= $editing ? 'Modifier le produit' : 'Ajouter un produit'; ?></h2>
        <form method="post" class="form-grid" enctype="multipart/form-data">
            <input type="hidden" name="intent" value="<?= $editing ? 'update' : 'create'; ?>">
            <?php if ($editing): ?>
                <input type="hidden" name="product_id" value="<?= (int) $editing['id']; ?>">
            <?php endif; ?>
            <label>Nom
                <input type="text" name="name" required value="<?= sanitize($editing['name'] ?? ''); ?>">
            </label>
            <label>Prix (DA)
                <input type="number" step="1" min="0" name="price" required
                    value="<?= sanitize((string) ($editing['price'] ?? '')); ?>">
            </label>
            <label>Catégorie
                <select name="category_id">
                    <option value="">— Aucune —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id']; ?>" <?= isset($editing['category_id']) && (int) $editing['category_id'] === (int) $cat['id'] ? 'selected' : ''; ?>>
                            <?= sanitize($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Image (fichier)
                <input type="file" name="image_file" accept="image/*">
            </label>
            <?php if (!empty($editing['image'])): ?>
                <div style="grid-column: 1 / -1; margin-top: .5rem; display:flex; gap:1rem; align-items:center;">
                    <div style="font-size: .9rem; color: var(--muted);">Image actuelle :</div>
                    <div><img src="<?= sanitize($editing['image']); ?>" alt="" style="max-height:60px; border-radius:4px;">
                    </div>
                </div>
            <?php endif; ?>
            <label style="grid-column: 1 / -1;">Description
                <textarea name="description" rows="3"><?= sanitize($editing['description'] ?? ''); ?></textarea>
            </label>
            <div style="grid-column: 1 / -1; display: flex; gap: 1rem;">
                <button class="btn btn-primary" type="submit"><?= $editing ? 'Mettre à jour' : 'Ajouter'; ?></button>
                <?php if ($editing): ?>
                    <a class="btn" href="products.php">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Créé le</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="5" class="empty-state">Aucune référence pour le moment.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= sanitize($product['name']); ?></td>
                            <td><?= sanitize($product['category_name'] ?? '—'); ?></td>
                            <td><?= number_format((float) $product['price'], 0, ',', ' '); ?> DA</td>
                            <td><?= sanitize(date('d/m/Y', strtotime($product['created_at']))); ?></td>
                            <td style="text-align: right;">
                                <a class="btn" href="products.php?edit=<?= (int) $product['id']; ?>">Modifier</a>
                                <form method="post" style="display: inline;"
                                    onsubmit="return confirm('Supprimer ce produit ?');">
                                    <input type="hidden" name="intent" value="delete">
                                    <input type="hidden" name="product_id" value="<?= (int) $product['id']; ?>">
                                    <button class="btn" type="submit"
                                        style="background: rgba(248,113,113,0.15); color: #f87171;">Supprimer</button>
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