<?php
declare(strict_types=1);

$pageTitle = 'Administrateurs';
$activePage = 'admins';
require __DIR__ . '/includes/header.php';

$message = '';
$isError = false;
$currentAdminId = (int)($_SESSION['admin_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $intent = $_POST['intent'] ?? '';

    try { 
        // a5dm compte
        if ($intent === 'create') {
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $password = (string)($_POST['password'] ?? '');

            if (!$email) {
                throw new InvalidArgumentException('Email invalide.');
            }

            if (strlen($password) < 8) {
                throw new InvalidArgumentException('Mot de passe trop court (8 caractères min).');
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $connection->prepare('INSERT INTO admins (email, hashed_pass) VALUES (?, ?)');

            if (!$stmt) {
                throw new RuntimeException('Erreur préparation SQL : ' . $connection->error);
            }

            $stmt->bind_param('ss', $email, $hash);
            $stmt->execute();
            $message = 'Administrateur ajouté avec succès.';
        } elseif ($intent === 'delete') {
            // fasi compte
            $adminId = (int)($_POST['admin_id'] ?? 0);

            if ($adminId <= 0) {
                throw new InvalidArgumentException('Administrateur introuvable.');
            }

            if ($adminId === $currentAdminId) {
                throw new InvalidArgumentException('Impossible de supprimer votre propre compte.');
            }

            $result = $connection->query('SELECT COUNT(*) AS total FROM admins');
            $totalAdmins = (int)($result?->fetch_assoc()['total'] ?? 0);

            if ($totalAdmins <= 1) {
                throw new InvalidArgumentException('Conservez au moins un administrateur actif.');
            }

            $stmt = $connection->prepare('DELETE FROM admins WHERE id = ?');

            if (!$stmt) {
                throw new RuntimeException('Erreur préparation SQL : ' . $connection->error);
            }

            $stmt->bind_param('i', $adminId);
            $stmt->execute();
            $message = 'Administrateur retiré.';
        }
    } catch (Throwable $exception) {
        $isError = true;
        $message = $exception instanceof InvalidArgumentException
            ? $exception->getMessage()
            : 'Impossible de traiter la demande.';
    }
}

$res = $connection->query('SELECT id, email, created_at FROM admins ORDER BY created_at DESC');
$admins = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$hasMultipleAdmins = count($admins) > 1;
?>

<section class="dashboard">
    <h1>Gestion des administrateurs</h1>
    <p style="color: var(--muted);">Ajoutez de nouveaux accès ou retirez des comptes en toute simplicité.</p>

    <?php if ($message): ?>
        <div class="alert <?= $isError ? 'alert-error' : 'alert-success'; ?>"><?= sanitize($message); ?></div>
    <?php endif; ?>

    <div class="table-card" style="margin-bottom: 2rem; padding: 2rem;">
        <h2>Ajouter un administrateur</h2>
        <form method="post" class="form-grid" style="align-items: flex-end;">
            <input type="hidden" name="intent" value="create">
            <label>Email professionnel
                <input type="email" name="email" required placeholder="ex. gestion@template.dz">
            </label>
            <label>Mot de passe
                <input type="password" name="password" minlength="8" required placeholder="8 caractères minimum">
            </label>
            <button class="btn btn-primary" type="submit">Créer le compte</button>
        </form>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Ajouté le</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($admins)): ?>
                <tr><td colspan="3" class="empty-state">Aucun administrateur enregistré.</td></tr>
            <?php else: ?>
                <?php foreach ($admins as $admin): ?>
                    <?php $isCurrent = (int)$admin['id'] === $currentAdminId; ?>
                    <tr>
                        <td>
                            <?= sanitize($admin['email']); ?>
                            <?php if ($isCurrent): ?>
                                <span style="display:inline-block; margin-left:0.5rem; font-size:0.8rem; color: var(--muted);">Vous</span>
                            <?php endif; ?>
                        </td>
                        <td><?= sanitize(date('d/m/Y', strtotime($admin['created_at']))); ?></td>
                        <td style="text-align: right;">
                            <?php if (!$isCurrent && $hasMultipleAdmins): ?>
                                <form method="post" onsubmit="return confirm('Retirer cet administrateur ?');" style="display: inline;">
                                    <input type="hidden" name="intent" value="delete">
                                    <input type="hidden" name="admin_id" value="<?= (int)$admin['id']; ?>">
                                    <button class="btn" type="submit" style="background: rgba(248,113,113,0.15); color: #b91c1c;">Supprimer</button>
                                </form>
                            <?php else: ?>
                                <span style="color: var(--muted); font-size: 0.85rem;">Action indisponible</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

