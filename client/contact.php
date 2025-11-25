<?php
declare(strict_types=1);
$pageTitle = 'Contact';
require __DIR__ . '/includes/header.php';

$errors = [];
$success = false;
$formData = [
    'phone' => '',
    'email' => '',
    'message' => '',
];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['message'] = trim($_POST['message'] ?? '');

    if ($formData['phone'] === '') {
        $errors[] = 'Merci d’indiquer un numéro de téléphone.';
    }

    if ($formData['email'] !== '' && !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Le format de l’adresse e-mail est invalide.';
    }

    if ($formData['message'] === '') {
        $errors[] = 'Merci de préciser votre demande.';
    }

    if (empty($errors)) {
        $stmt = $connection->prepare('INSERT INTO contacts (phone, email, message) VALUES (?, ?, ?)');

        if ($stmt === false) {
            $errors[] = 'Une erreur technique empêche l’envoi. Réessayez plus tard.';
        } else {
            $emailValue = $formData['email'] !== '' ? $formData['email'] : null;
            $stmt->bind_param('sss', $formData['phone'], $emailValue, $formData['message']);

            if ($stmt->execute()) {
                $success = true;
                $formData = ['phone' => '', 'email' => '', 'message' => ''];
            } else {
                $errors[] = 'Impossible d’enregistrer votre message pour le moment.';
            }

            $stmt->close();
        }
    }
}


$contactDetails = [
    [
        'label' => 'Téléphone',
        'value' => '+213 770 00 00 00',
        'hint' => 'Disponible 7j/7 de 9h à 21h',
    ],
    [
        'label' => 'E-mail',
        'value' => 'hello@dz.dz',
        'hint' => 'Nous répondons sous 24h ouvrées',
    ],
    [
        'label' => 'Adresse',
        'value' => 'Boumerdès, INIM',
        'hint' => 'Block 2',
    ],
    [
        'label' => 'Horaires',
        'value' => 'Dimanche – Jeudi',
        'hint' => '09h00 – 16h30',
    ],
];
?>

<style>
    .contact-highlight {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
        padding: 2rem;
        background: var(--card-bg, #fff);
        border-radius: 18px;
        box-shadow: 0 25px 60px rgba(15, 20, 40, 0.08);
    }

    .contact-highlight h3 {
        margin-top: 0;
        font-size: 1.4rem;
    }

    .contact-details {
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
    }

    .contact-details-item span {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.2em;
        color: var(--muted);
        display: block;
        margin-bottom: 0.25rem;
    }

    .contact-details-item strong {
        font-size: 1.1rem;
    }

    .contact-details-item small {
        display: block;
        margin-top: 0.2rem;
        color: var(--muted);
    }

    .contact-map iframe {
        width: 100%;
        min-height: 320px;
        border: 0;
        border-radius: 16px;
    }

    .success {
        color: #22c55e;
    }

    .error {
        color: #f87171;
    }

    .contact-form-card {

        margin: 2rem;
        padding: 2rem;
        border-radius: 18px;
        box-shadow: 0 35px 70px rgba(15, 20, 40, 0.09);
    }

    @media (max-width: 640px) {
        .contact-highlight {
            padding: 1.5rem;
        }

        .contact-form-card {
            padding: 1.5rem;
        }
    }
</style>

<main>
    <div class="section-title">
        <div>
            <p style="color: var(--muted); text-transform: uppercase; letter-spacing: 0.2em; margin: 0;">Contact</p>
            <h2>Contacter nous</h2>
        </div>
        <p style="max-width: 360px; color: var(--muted);">
            Laissez-nous vos coordonnées et un message. Nous répondons sous 24h ouvrées pour confirmer vos besoins.
        </p>
    </div>



    <section class="card contact-form-card" style="max-width: 820px; margin: 2rem auto;">

        <?php if ($success): ?>
            <div class="alert success">
                Votre message a bien été envoyé. Nous reviendrons vers vous très vite.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= sanitize($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" class="form-grid">
            <label>
                <span>Téléphone *</span>
                <input type="text" name="phone" value="<?= sanitize($formData['phone']); ?>" required
                    placeholder="+213 6 00 00 00 00">
            </label>

            <label>
                <span>E-mail</span>
                <input type="email" name="email" value="<?= sanitize($formData['email']); ?>"
                    placeholder="vous@example.com">
            </label>

            <label style="grid-column: 1 / -1;">
                <span>Message *</span>
                <textarea name="message" rows="5" required
                    placeholder="Expliquez vos besoins, nous vous rappelons au plus vite."><?= sanitize($formData['message']); ?></textarea>
            </label>

            <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">Envoyer</button>
            </div>
        </form>
    </section>

    <section class="contact-highlight">
        <div>
            <h3>Rencontrons-nous à l'univ</h3>
            <p style="color: var(--muted); line-height: 1.6;">
                Lorem, ipsum dolor sit amet consectetur adipisicing elit. Alias, voluptates necessitatibus odit est repudiandae non at numquam a! Sapiente deserunt aliquam mollitia vitae fugiat natus corrupti repellat tempora velit blanditiis!</p>
            <div class="contact-details">
                <?php foreach ($contactDetails as $detail): ?>
                    <div class="contact-details-item">
                        <span><?= sanitize($detail['label']); ?></span>
                        <strong><?= sanitize($detail['value']); ?></strong>
                        <small><?= sanitize($detail['hint']); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="contact-map">
            <iframe title="Boumerdès - Boutique" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps?q=Boumerd%C3%A8s%2C%20Alg%C3%A9rie&output=embed"></iframe>
        </div>
    </section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>