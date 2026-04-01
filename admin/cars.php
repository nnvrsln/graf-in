<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

adminRequireAuth();

function adminImageSrc(string $imageUrl): string
{
    $imageUrl = trim($imageUrl);
    if ($imageUrl === '') {
        return '/assets/uploads/cars/.gitkeep';
    }

    if (preg_match('#^https?://#i', $imageUrl)) {
        return $imageUrl;
    }

    return '/' . ltrim($imageUrl, '/');
}

function adminCarImageAbsolutePath(string $imageUrl): ?string
{
    $normalized = str_replace('\\', '/', trim($imageUrl));
    if ($normalized === '') {
        return null;
    }

    if (strpos($normalized, 'assets/uploads/cars/') !== 0) {
        return null;
    }

    return dirname(__DIR__) . '/' . $normalized;
}

$search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$categoryFilter = isset($_GET['category']) ? trim((string) $_GET['category']) : '';
$categories = carsCategoryList();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['csrf']) ? (string) $_POST['csrf'] : '';
    $action = isset($_POST['action']) ? (string) $_POST['action'] : '';
    $returnSearch = isset($_POST['return_q']) ? trim((string) $_POST['return_q']) : '';
    $returnCategory = isset($_POST['return_category']) ? trim((string) $_POST['return_category']) : '';

    $redirectParams = [];
    if ($returnSearch !== '') {
        $redirectParams['q'] = $returnSearch;
    }
    if ($returnCategory !== '') {
        $redirectParams['category'] = $returnCategory;
    }

    if (!adminVerifyCsrf($token)) {
        adminFlashSet('error', 'Сессия устарела. Повторите действие.');
    } elseif ($action === 'delete') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            adminFlashSet('error', 'Некорректный ID автомобиля.');
        } else {
            $car = findCarById($pdo, $id);
            if ($car === null) {
                adminFlashSet('error', 'Автомобиль не найден.');
            } else {
                deleteCarById($pdo, $id);

                $absoluteImage = adminCarImageAbsolutePath((string) ($car['image_url'] ?? ''));
                if ($absoluteImage !== null && is_file($absoluteImage)) {
                    @unlink($absoluteImage);
                }

                adminFlashSet('success', 'Автомобиль удален.');
            }
        }
    }

    $redirect = '/admin/cars.php';
    if ($redirectParams !== []) {
        $redirect .= '?' . http_build_query($redirectParams);
    }

    header('Location: ' . $redirect);
    exit;
}

$cars = fetchCarsForAdmin(
    $pdo,
    $search !== '' ? $search : null,
    $categoryFilter !== '' ? $categoryFilter : null
);

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
    <title>Админка • Автомобили</title>
    <link rel="stylesheet" href="/assets/css/admin-panel.css">
</head>

<body class="admin-page">
    <header class="admin-topbar">
        <div class="admin-topbar-left">
            <h1>Автомобили</h1>
            <p>Управление каталогом и категориями автопарка.</p>
        </div>
        <div class="admin-topbar-actions">
            <a class="admin-btn" href="/admin/car-edit.php">Добавить авто</a>
            <a class="admin-btn is-ghost" href="/admin/settings.php">Общие настройки</a>
            <a class="admin-btn is-ghost" href="/admin/seo.php">SEO</a>
            <a class="admin-btn is-ghost" href="/admin/logout.php">Выйти</a>
        </div>
    </header>

    <main class="admin-main">
        <?php if (is_array($flash)): ?>
        <div class="admin-alert <?= isset($flash['type']) && $flash['type'] === 'success' ? 'is-success' : 'is-error' ?>">
            <?= e((string) ($flash['message'] ?? '')) ?>
        </div>
        <?php endif; ?>

        <section class="admin-card">
            <form class="admin-filters" method="get" action="/admin/cars.php">
                <label>
                    <span>Поиск</span>
                    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Название или описание">
                </label>

                <label>
                    <span>Категория</span>
                    <select name="category">
                        <option value="">Все</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category) ?>"<?= $categoryFilter === $category ? ' selected' : '' ?>><?= e($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <div class="admin-filter-actions">
                    <button type="submit" class="admin-btn is-small">Применить</button>
                    <a class="admin-btn is-small is-ghost" href="/admin/cars.php">Сбросить</a>
                </div>
            </form>
        </section>

        <section class="admin-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Фото</th>
                            <th>Название</th>
                            <th>Категория</th>
                            <th>Параметры</th>
                            <th>Цена</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($cars === []): ?>
                        <tr>
                            <td colspan="7" class="admin-empty">Нет автомобилей по выбранным фильтрам.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($cars as $car): ?>
                        <tr>
                            <td class="admin-photo-cell" data-label="Фото">
                                <img class="admin-car-photo" src="<?= e(adminImageSrc((string) $car['image_url'])) ?>" alt="<?= e((string) $car['name']) ?>">
                            </td>
                            <td data-label="Название">
                                <strong><?= e((string) $car['name']) ?></strong>
                                <div class="admin-muted">ID: <?= e((string) $car['id']) ?></div>
                            </td>
                            <td data-label="Категория"><?= e((string) $car['category']) ?></td>
                            <td data-label="Параметры">
                                <?= e(number_format((float) $car['engine_volume'], 1, '.', '')) ?> л,
                                <?= e((string) $car['drive_type']) ?>,
                                <?= e((string) $car['fuel_type']) ?>
                            </td>
                            <td data-label="Цена"><?= e(adminFormatPrice((int) $car['price_per_day'])) ?> ₽</td>
                            <td data-label="Статус">
                                <?php if ((int) ($car['is_active'] ?? 0) === 1): ?>
                                <span class="admin-chip is-active">Активен</span>
                                <?php else: ?>
                                <span class="admin-chip is-inactive">Скрыт</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Действия">
                                <div class="admin-row-actions">
                                    <a class="admin-btn is-small" href="/admin/car-edit.php?id=<?= e((string) $car['id']) ?>">Редактировать</a>
                                    <form method="post" action="/admin/cars.php" onsubmit="return confirm('Удалить автомобиль?');">
                                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= e((string) $car['id']) ?>">
                                        <input type="hidden" name="return_q" value="<?= e($search) ?>">
                                        <input type="hidden" name="return_category" value="<?= e($categoryFilter) ?>">
                                        <button type="submit" class="admin-btn is-small is-danger">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>

</html>
