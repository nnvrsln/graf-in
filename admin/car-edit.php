<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

adminRequireAuth();

function adminUploadedImageAbsolutePath(string $imageUrl): ?string
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

function adminImagePreviewSrc(string $imageUrl): string
{
    $imageUrl = trim($imageUrl);
    if ($imageUrl === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $imageUrl)) {
        return $imageUrl;
    }

    return '/' . ltrim($imageUrl, '/');
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;

$categories = carsCategoryList();
$driveTypes = carsDriveTypeList();
$fuelTypes = carsFuelTypeList();

$car = [
    'id' => 0,
    'name' => '',
    'category' => $categories[0],
    'description' => '',
    'engine_volume' => '2.0',
    'drive_type' => $driveTypes[0],
    'fuel_type' => $fuelTypes[0],
    'price_per_day' => '0',
    'image_url' => '',
    'is_active' => 1,
];

if ($isEdit) {
    $existing = findCarById($pdo, $id);
    if ($existing === null) {
        adminFlashSet('error', 'Автомобиль не найден.');
        header('Location: /admin/cars.php');
        exit;
    }

    $car = array_merge($car, $existing);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['csrf']) ? (string) $_POST['csrf'] : '';

    if (!adminVerifyCsrf($token)) {
        $errors[] = 'Сессия устарела. Обновите страницу и попробуйте снова.';
    }

    $car['name'] = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
    $car['category'] = isset($_POST['category']) ? trim((string) $_POST['category']) : '';
    $car['description'] = isset($_POST['description']) ? trim((string) $_POST['description']) : '';
    $car['engine_volume'] = isset($_POST['engine_volume']) ? str_replace(',', '.', trim((string) $_POST['engine_volume'])) : '';
    $car['drive_type'] = isset($_POST['drive_type']) ? trim((string) $_POST['drive_type']) : '';
    $car['fuel_type'] = isset($_POST['fuel_type']) ? trim((string) $_POST['fuel_type']) : '';
    $car['price_per_day'] = isset($_POST['price_per_day']) ? trim((string) $_POST['price_per_day']) : '';
    $car['is_active'] = isset($_POST['is_active']) ? 1 : 0;

    if ($car['name'] === '') {
        $errors[] = 'Введите название автомобиля.';
    }

    if (!in_array($car['category'], $categories, true)) {
        $errors[] = 'Выберите корректную категорию.';
    }

    if ($car['description'] === '') {
        $errors[] = 'Добавьте описание автомобиля.';
    }

    if (!is_numeric($car['engine_volume']) || (float) $car['engine_volume'] <= 0) {
        $errors[] = 'Укажите корректный объем двигателя.';
    }

    if (!in_array($car['drive_type'], $driveTypes, true)) {
        $errors[] = 'Выберите корректный тип привода.';
    }

    if (!in_array($car['fuel_type'], $fuelTypes, true)) {
        $errors[] = 'Выберите корректный тип топлива.';
    }

    if (!ctype_digit($car['price_per_day']) || (int) $car['price_per_day'] <= 0) {
        $errors[] = 'Укажите корректную цену за сутки.';
    }

    $newImagePath = null;
    if (isset($_FILES['image_file']) && is_array($_FILES['image_file'])) {
        $uploadError = (int) ($_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($uploadError !== UPLOAD_ERR_NO_FILE) {
            if ($uploadError !== UPLOAD_ERR_OK) {
                $errors[] = 'Не удалось загрузить изображение. Попробуйте еще раз.';
            } else {
                $tmpPath = (string) ($_FILES['image_file']['tmp_name'] ?? '');
                $fileSize = (int) ($_FILES['image_file']['size'] ?? 0);

                if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
                    $errors[] = 'Файл изображения поврежден.';
                } elseif ($fileSize <= 0 || $fileSize > 8 * 1024 * 1024) {
                    $errors[] = 'Размер файла должен быть до 8 МБ.';
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = (string) $finfo->file($tmpPath);
                    $allowed = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        'image/gif' => 'gif',
                    ];

                    if (!isset($allowed[$mimeType])) {
                        $errors[] = 'Допустимы только JPG, PNG, WEBP или GIF.';
                    } else {
                        $uploadDir = dirname(__DIR__) . '/assets/uploads/cars';
                        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                            $errors[] = 'Не удалось создать папку для загрузки изображений.';
                        } else {
                            $fileName = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mimeType];
                            $targetAbs = $uploadDir . '/' . $fileName;

                            if (!move_uploaded_file($tmpPath, $targetAbs)) {
                                $errors[] = 'Не удалось сохранить изображение на сервере.';
                            } else {
                                $newImagePath = 'assets/uploads/cars/' . $fileName;
                            }
                        }
                    }
                }
            }
        }
    }

    if (!$isEdit && $newImagePath === null) {
        $errors[] = 'Для нового автомобиля выберите фото из галереи.';
    }

    if ($errors === []) {
        $previousImage = (string) ($car['image_url'] ?? '');
        if ($newImagePath !== null) {
            $car['image_url'] = $newImagePath;
        }

        $payload = [
            'name' => $car['name'],
            'category' => $car['category'],
            'description' => $car['description'],
            'engine_volume' => (float) $car['engine_volume'],
            'drive_type' => $car['drive_type'],
            'fuel_type' => $car['fuel_type'],
            'price_per_day' => (int) $car['price_per_day'],
            'image_url' => (string) $car['image_url'],
            'is_active' => (int) $car['is_active'],
        ];

        if ($isEdit) {
            updateCar($pdo, (int) $car['id'], $payload);
            adminFlashSet('success', 'Автомобиль обновлен.');

            if ($newImagePath !== null) {
                $oldAbs = adminUploadedImageAbsolutePath($previousImage);
                $newAbs = adminUploadedImageAbsolutePath($newImagePath);
                if ($oldAbs !== null && $newAbs !== null && $oldAbs !== $newAbs && is_file($oldAbs)) {
                    @unlink($oldAbs);
                }
            }
        } else {
            createCar($pdo, $payload);
            adminFlashSet('success', 'Новый автомобиль добавлен.');
        }

        header('Location: /admin/cars.php');
        exit;
    }
}

$flash = adminFlashPull();
$csrf = adminCsrfToken();
$pageTitle = $isEdit ? 'Редактирование авто' : 'Добавление авто';
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
    <title>Админка • <?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="/assets/css/admin-panel.css">
</head>

<body class="admin-page">
    <header class="admin-topbar">
        <div class="admin-topbar-left">
            <h1><?= e($pageTitle) ?></h1>
            <p>Заполните данные автомобиля и сохраните изменения.</p>
        </div>
        <div class="admin-topbar-actions">
            <a class="admin-btn is-ghost" href="/admin/cars.php">Назад к списку</a>
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
            <form class="admin-form" method="post" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

                <div class="admin-form-grid">
                    <label>
                        <span>Название</span>
                        <input type="text" name="name" value="<?= e((string) $car['name']) ?>" required>
                    </label>

                    <label>
                        <span>Категория</span>
                        <select name="category" required>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category) ?>"<?= (string) $car['category'] === $category ? ' selected' : '' ?>><?= e($category) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <span>Объем двигателя (л)</span>
                        <input type="number" step="0.1" min="0.1" name="engine_volume" value="<?= e((string) $car['engine_volume']) ?>" required>
                    </label>

                    <label>
                        <span>Привод</span>
                        <select name="drive_type" required>
                            <?php foreach ($driveTypes as $driveType): ?>
                            <option value="<?= e($driveType) ?>"<?= (string) $car['drive_type'] === $driveType ? ' selected' : '' ?>><?= e($driveType) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <span>Тип топлива</span>
                        <select name="fuel_type" required>
                            <?php foreach ($fuelTypes as $fuelType): ?>
                            <option value="<?= e($fuelType) ?>"<?= (string) $car['fuel_type'] === $fuelType ? ' selected' : '' ?>><?= e($fuelType) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <span>Цена за сутки (₽)</span>
                        <input type="number" min="1" step="1" name="price_per_day" value="<?= e((string) $car['price_per_day']) ?>" required>
                    </label>

                    <label>
                        <span>Фото автомобиля</span>
                        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif">
                        <small>Выберите фото из галереи/файлов устройства (до 8 МБ).</small>
                    </label>
                </div>

                <label>
                    <span>Описание</span>
                    <textarea name="description" rows="5" required><?= e((string) $car['description']) ?></textarea>
                </label>

                <label class="admin-checkbox">
                    <input type="checkbox" name="is_active" value="1"<?= (int) $car['is_active'] === 1 ? ' checked' : '' ?>>
                    <span>Показывать автомобиль в каталоге</span>
                </label>

                <?php if ((string) $car['image_url'] !== ''): ?>
                <div class="admin-current-image">
                    <p>Текущее фото:</p>
                    <img src="<?= e(adminImagePreviewSrc((string) $car['image_url'])) ?>" alt="Текущее фото авто">
                </div>
                <?php endif; ?>

                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn">Сохранить</button>
                    <a class="admin-btn is-ghost" href="/admin/cars.php">Отмена</a>
                </div>
            </form>
        </section>
    </main>
</body>

</html>
