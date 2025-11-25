<?php
declare(strict_types=1);
$pageTitle = 'Contacts';
$activePage = 'contacts';
require __DIR__ . '/includes/header.php';

$res = $connection->query('SELECT phone, email, message, date_contact FROM contacts ORDER BY date_contact DESC');
$contacts = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>

<section class="dashboard">
    <h1>Messages clients</h1>
    <p style="color: var(--muted);">Tous les messages envoyés via le formulaire public.</p>

    <div class="table-card">
        <table>
            <thead>
            <tr>
                <th>Coordonnées</th>
                <th>Message</th>
                <th>Reçu le</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($contacts)): ?>
                <tr><td colspan="3" class="empty-state">Aucun message pour le moment.</td></tr>
            <?php else: ?>
                <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td>
                            <strong><?= sanitize($contact['phone']); ?></strong><br>
                            <?= sanitize($contact['email'] ?? ''); ?>
                        </td>
                        <td><?= nl2br(sanitize($contact['message'])); ?></td>
                        <td><?= sanitize(date('d/m/Y H:i', strtotime($contact['date_contact']))); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

