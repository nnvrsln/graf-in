<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

adminRequireAuth();

$configFilePath = dirname(__DIR__) . '/config/config.php';
$pageOptions = siteSeoPageOptions();
$editable = siteEditableSettingsFromConfig($config);
$contacts = $editable['contacts'];
$bookingDestination = $editable['booking_destination'];
$seoSettings = $editable['seo'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['csrf']) ? (string) $_POST['csrf'] : '';

    if (!adminVerifyCsrf($token)) {
        $errors[] = 'Сессия устарела. Обновите страницу и повторите попытку.';
    }

    foreach ($pageOptions as $pageKey => $pageLabel) {
        $title = trim((string) ($_POST['seo_' . $pageKey . '_title'] ?? ''));
        $description = trim((string) ($_POST['seo_' . $pageKey . '_description'] ?? ''));
        $keywords = trim((string) ($_POST['seo_' . $pageKey . '_keywords'] ?? ''));
        $robots = trim((string) ($_POST['seo_' . $pageKey . '_robots'] ?? ''));

        $seoSettings[$pageKey] = [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'robots' => $robots,
        ];

        if ($title === '') {
            $errors[] = 'Заполните Title для страницы: ' . $pageLabel . '.';
        }

        if ($description === '') {
            $errors[] = 'Заполните Description для страницы: ' . $pageLabel . '.';
        }
    }

    if ($errors === []) {
        try {
            siteSaveEditableSettings($configFilePath, [
                'contacts' => $contacts,
                'booking_destination' => $bookingDestination,
                'seo' => $seoSettings,
            ]);

            adminFlashSet('success', 'SEO-настройки сохранены.');
            header('Location: /admin/seo.php');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Не удалось сохранить SEO-настройки. Проверьте права на запись в config/site-settings.php.';
        }
    }
}

$seoSettings = siteNormalizeSeoSettings($seoSettings);
$flash = adminFlashPull();
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
    <title>Админка • SEO</title>
    <link rel="stylesheet" href="/assets/css/admin-panel.css">
</head>

<body class="admin-page">
    <header class="admin-topbar">
        <div class="admin-topbar-left">
            <h1>SEO-настройки</h1>
            <p>Управление Title, Description и мета-тегами для основных страниц сайта.</p>
        </div>
        <div class="admin-topbar-actions">
            <a class="admin-btn is-ghost" href="/admin/cars.php">Автомобили</a>
            <a class="admin-btn is-ghost" href="/admin/settings.php">Настройки</a>
            <a class="admin-btn is-ghost" href="/admin/logout.php">Выйти</a>
        </div>
    </header>

    <main class="admin-main">
        <?php if (is_array($flash)): ?>
        <div
            class="admin-alert <?= isset($flash['type']) && $flash['type'] === 'success' ? 'is-success' : 'is-error' ?>">
            <?= e((string) ($flash['message'] ?? '')) ?>
        </div>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
        <div class="admin-alert is-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <section class="admin-card">
            <form class="admin-form" method="post" novalidate>
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

                <?php foreach ($pageOptions as $pageKey => $pageLabel): ?>
                <?php $seo = $seoSettings[$pageKey] ?? []; ?>
                <div
                    style="border: 1px solid var(--admin-border); border-radius: 12px; padding: 14px; display: grid; gap: 12px;">
                    <h2 style="margin: 0; font-size: 1rem;"><?= e($pageLabel) ?></h2>
                    <div class="admin-form-grid">
                        <label>
                            <span>Title</span>
                            <input type="text" name="seo_<?= e($pageKey) ?>_title"
                                value="<?= e((string) ($seo['title'] ?? '')) ?>" required>
                        </label>

                        <label>
                            <span>Meta robots</span>
                            <input type="text" name="seo_<?= e($pageKey) ?>_robots"
                                value="<?= e((string) ($seo['robots'] ?? 'index,follow')) ?>"
                                placeholder="index,follow">
                        </label>

                        <label style="grid-column: 1 / -1;">
                            <span>Meta description</span>
                            <textarea name="seo_<?= e($pageKey) ?>_description" rows="3"
                                required><?= e((string) ($seo['description'] ?? '')) ?></textarea>
                        </label>

                        <label style="grid-column: 1 / -1;">
                            <span>Meta keywords</span>
                            <textarea name="seo_<?= e($pageKey) ?>_keywords"
                                rows="3"><?= e((string) ($seo['keywords'] ?? '')) ?></textarea>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn">Сохранить SEO</button>
                </div>
            </form>
        </section>
    </main>
</body>

</html>