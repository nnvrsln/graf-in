<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/admin_auth.php';

adminBootSession();
header('Content-Type: text/html; charset=UTF-8');

if (adminIsAuthenticated()) {
    header('Location: /admin/cars.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['csrf']) ? (string) $_POST['csrf'] : '';
    $username = isset($_POST['username']) ? trim((string) $_POST['username']) : '';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';

    if (!adminVerifyCsrf($token)) {
        $error = 'Сессия устарела. Обновите страницу и попробуйте снова.';
    } elseif ($username === '' || $password === '') {
        $error = 'Введите логин и пароль.';
    } elseif (!adminAttemptLogin($username, $password)) {
        $error = 'Неверный логин или пароль.';
    } else {
        adminFlashSet('success', 'Вход выполнен успешно.');
        header('Location: /admin/cars.php');
        exit;
    }
}

$csrf = adminCsrfToken();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
    <link rel="manifest" href="/assets/site.webmanifest">
    <meta name="theme-color" content="#111827">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="DadaevCAR">
    <title>Админка • Вход</title>
    <link rel="stylesheet" href="/assets/css/admin-panel.css">
</head>

<body class="admin-login-page">
    <main class="admin-login-wrap">
        <section class="admin-login-card">
            <div class="admin-login-head">
                <h1>Панель управления</h1>
                <p>Войдите, чтобы управлять каталогом автомобилей.</p>
            </div>

            <?php if ($error !== ''): ?>
            <div class="admin-alert is-error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="post" class="admin-form admin-login-form" novalidate>
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

                <label>
                    <span>Логин</span>
                    <input type="text" name="username" autocomplete="username" required>
                </label>

                <label>
                    <span>Пароль</span>
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>

                <button type="submit" class="admin-btn">Войти</button>
            </form>
        </section>
    </main>
</body>

</html>