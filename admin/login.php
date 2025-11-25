<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$error = '';

// ida connecter deja
if (is_admin_authenticated()) {
    redirect('products.php');
}

// jib les infor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') { // info na9sa
        $error = 'Merci de renseigner vos identifiants.';
    } else {
        $stmt = $connection->prepare('SELECT id, email, hashed_pass FROM admins WHERE email = ?');

        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $admin = $stmt->get_result()?->fetch_assoc();
        } else {
            $admin = null;
        }

        if ($admin && password_verify($password, $admin['hashed_pass'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            redirect('products.php');
        } else {
            $error = 'Identifiants incorrects.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion admin — Template</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<div class="auth-card">
    <h1>Espace administration Template</h1>
    <p style="color: var(--muted);">Entrez vos identifiants pour accéder au tableau de bord.</p>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= sanitize($error); ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
        <label>
            Email professionnel
            <input type="email" name="email" required>
        </label>
        <label>
            Mot de passe
            <input type="password" name="password" required>
        </label>
        <button class="btn btn-primary" type="submit">Se connecter</button>
    </form>
    <p style="margin-top: 1.5rem;">
        <a href="../client/index.php" style="color: var(--muted);">Retour au site</a>
    </p>
</div>
</body>
</html>

