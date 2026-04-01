<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

adminRequireAuth();

$configFilePath = dirname(__DIR__) . '/config/config.php';
$fieldLabels = [
    'address' => 'Полный адрес',
    'address_short' => 'Короткий адрес',
    'phone' => 'Телефон (отображение)',
    'phone_href' => 'Телефон для ссылки (без пробелов)',
    'telegram' => 'Telegram (ник/подпись)',
    'telegram_link' => 'Ссылка Telegram',
    'whatsapp' => 'WhatsApp (отображение)',
    'whatsapp_link' => 'Ссылка WhatsApp',
    'work_time' => 'Время работы',
    'contact_note' => 'Примечание в контактах',
];

$bookingOptions = siteBookingDestinationOptions();
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

    foreach ($fieldLabels as $key => $label) {
        $value = trim((string) ($_POST[$key] ?? ''));
        $contacts[$key] = $value;

        if ($value === '') {
            $errors[] = 'Поле «' . $label . '» не должно быть пустым.';
        }
    }

    $bookingDestination = siteNormalizeBookingDestination((string) ($_POST['booking_destination'] ?? 'whatsapp'));

    if (!array_key_exists($bookingDestination, $bookingOptions)) {
        $errors[] = 'Выберите корректное направление для кнопки «Забронировать».';
    }

    if ($errors === []) {
        try {
            siteSaveEditableSettings($configFilePath, [
                'contacts' => $contacts,
                'booking_destination' => $bookingDestination,
                'seo' => $seoSettings,
            ]);

            adminFlashSet('success', 'Общие настройки сохранены.');
            header('Location: /admin/settings.php');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Не удалось сохранить настройки. Проверьте права на запись в config/site-settings.php.';
        }
    }
}

$flash = adminFlashPull();
$csrf = adminCsrfToken();

$previewConfig = $config;
$previewConfig['contacts'] = $contacts;
$previewConfig['booking_destination'] = $bookingDestination;
$previewCta = siteBuildBookingCta($previewConfig);
$previewChannel = siteBookingDestinationLabel((string) $previewCta['destination']);
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
    <title>Админка • Общие настройки</title>
    <link rel="stylesheet" href="/assets/css/admin-panel.css">
</head>

<body class="admin-page">
    <header class="admin-topbar">
        <div class="admin-topbar-left">
            <h1>Общие настройки сайта</h1>
            <p>Управление контактами и направлением кнопки «Забронировать» в карточках авто.</p>
        </div>
        <div class="admin-topbar-actions">
            <a class="admin-btn is-ghost" href="/admin/cars.php">Автомобили</a>
            <a class="admin-btn is-ghost" href="/admin/seo.php">SEO</a>
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

                <div class="admin-form-grid">
                    <?php foreach ($fieldLabels as $key => $label): ?>
                    <label>
                        <span><?= e($label) ?></span>
                        <?php if ($key === 'contact_note'): ?>
                        <textarea name="<?= e($key) ?>" rows="4"
                            required><?= e((string) ($contacts[$key] ?? '')) ?></textarea>
                        <?php else: ?>
                        <input type="text" name="<?= e($key) ?>" value="<?= e((string) ($contacts[$key] ?? '')) ?>"
                            required>
                        <?php endif; ?>
                    </label>
                    <?php endforeach; ?>

                    <label>
                        <span>Куда ведет кнопка «Забронировать»</span>
                        <select name="booking_destination" required>
                            <?php foreach ($bookingOptions as $value => $title): ?>
                            <option value="<?= e($value) ?>" <?= $bookingDestination === $value ? ' selected' : '' ?>>
                                <?= e($title) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn">Сохранить настройки</button>
                </div>
            </form>
        </section>

        <section class="admin-card">
            <h2 style="margin: 0 0 8px; font-size: 1.05rem;">Предпросмотр кнопки «Забронировать»</h2>
            <p class="admin-muted" style="margin-top: 0;">Сейчас канал: <strong><?= e($previewChannel) ?></strong></p>
            <p class="admin-muted" style="word-break: break-all;">Ссылка: <?= e((string) $previewCta['href']) ?></p>
            <a class="admin-btn" href="<?= e((string) $previewCta['href']) ?>"
                <?= (string) $previewCta['target'] !== '' ? ' target="' . e((string) $previewCta['target']) . '"' : '' ?><?= (string) $previewCta['rel'] !== '' ? ' rel="' . e((string) $previewCta['rel']) . '"' : '' ?>>Проверить
                ссылку</a>
        </section>
    </main>
</body>

</html>