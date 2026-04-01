<?php

declare(strict_types=1);

function fetchCars(PDO $pdo, ?string $category = null, int $limit = 50): array
{
    $limit = max(1, min($limit, 200));

    $sql = 'SELECT id, category, name, description, engine_volume, drive_type, fuel_type, price_per_day, image_url, is_rented, rent_end_at,
                   CASE WHEN is_rented = 1 AND rent_end_at IS NOT NULL AND rent_end_at > NOW() THEN 1 ELSE 0 END AS rent_active
            FROM cars
            WHERE is_active = 1';

    $params = [];

    if ($category !== null && $category !== '') {
        $sql .= ' AND category = :category';
        $params['category'] = $category;
    }

    $sql .= ' ORDER BY id DESC LIMIT ' . $limit;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function countActiveCars(PDO $pdo): int
{
    $stmt = $pdo->query('SELECT COUNT(*) FROM cars WHERE is_active = 1');

    return (int) $stmt->fetchColumn();
}

function fetchCatalogPreviewCars(PDO $pdo, int $limit = 6): array
{
    $limit = max(1, min($limit, 12));

    $cars = $pdo->query(
        'SELECT id, category, name, description, engine_volume, drive_type, fuel_type, price_per_day, image_url
         FROM cars
         WHERE is_active = 1
         ORDER BY id DESC'
    )->fetchAll();

    if ($cars === []) {
        return [];
    }

    $categoryOrder = carsCategoryList();
    $selected = [];
    $selectedIds = [];

    foreach ($categoryOrder as $category) {
        foreach ($cars as $car) {
            $carId = (int) ($car['id'] ?? 0);
            if ($carId <= 0 || isset($selectedIds[$carId])) {
                continue;
            }

            if ((string) ($car['category'] ?? '') !== $category) {
                continue;
            }

            $selected[] = $car;
            $selectedIds[$carId] = true;
            break;
        }
    }

    $remaining = array_values(array_filter($cars, static function (array $car) use ($selectedIds): bool {
        $carId = (int) ($car['id'] ?? 0);

        return $carId > 0 && !isset($selectedIds[$carId]);
    }));

    $primaryTarget = min(count($categoryOrder), $limit);
    while (count($selected) < $primaryTarget && $remaining !== []) {
        $next = array_shift($remaining);
        if ($next === null) {
            break;
        }

        $nextId = (int) ($next['id'] ?? 0);
        if ($nextId <= 0 || isset($selectedIds[$nextId])) {
            continue;
        }

        $selected[] = $next;
        $selectedIds[$nextId] = true;
    }

    if (count($selected) < $limit && $remaining !== []) {
        $randomIndex = random_int(0, count($remaining) - 1);
        $randomCar = $remaining[$randomIndex];
        $randomId = (int) ($randomCar['id'] ?? 0);

        if ($randomId > 0 && !isset($selectedIds[$randomId])) {
            $selected[] = $randomCar;
            $selectedIds[$randomId] = true;
        }

        array_splice($remaining, $randomIndex, 1);
    }

    while (count($selected) < $limit && $remaining !== []) {
        $next = array_shift($remaining);
        if ($next === null) {
            break;
        }

        $nextId = (int) ($next['id'] ?? 0);
        if ($nextId <= 0 || isset($selectedIds[$nextId])) {
            continue;
        }

        $selected[] = $next;
        $selectedIds[$nextId] = true;
    }

    return array_slice($selected, 0, $limit);
}

function carsHasColumn(PDO $pdo, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :table
           AND COLUMN_NAME = :column
         LIMIT 1'
    );

    $stmt->execute([
        'table' => 'cars',
        'column' => $column,
    ]);

    return (bool) $stmt->fetchColumn();
}

function carsEnumSql(array $values): string
{
    return implode(', ', array_map(static function (string $value): string {
        return "'" . str_replace("'", "''", $value) . "'";
    }, $values));
}

function ensureCarsCategorySchema(PDO $pdo): void
{
    if (!carsHasColumn($pdo, 'category')) {
        return;
    }

    if (carsHasColumn($pdo, 'car_class')) {
        $pdo->exec("UPDATE cars SET category = COALESCE(NULLIF(TRIM(category), ''), car_class)");
        $pdo->exec('ALTER TABLE cars DROP COLUMN car_class');
    }

    $pdo->exec('ALTER TABLE cars MODIFY category VARCHAR(64) NOT NULL');

    $pdo->exec(
        "UPDATE cars
         SET category = CASE
            WHEN category IN ('Премиум', 'Купе') THEN 'Премиум'
            WHEN category IN ('Внедорожники', 'Внедорожник', 'Кроссовер', 'Пикап') THEN 'Внедорожники'
            WHEN category IN ('Бизнес') THEN 'Бизнес'
            WHEN category IN ('Седаны') THEN 'Седаны'
            WHEN category IN ('Минивэны', 'Минивэн') THEN 'Минивэны'
            WHEN category IN ('Седан', 'Хэтчбек') AND price_per_day >= 15000 THEN 'Премиум'
            WHEN category IN ('Седан', 'Хэтчбек') AND price_per_day >= 8500 THEN 'Бизнес'
            WHEN category IN ('Седан', 'Хэтчбек') THEN 'Седаны'
            ELSE 'Седаны'
         END"
    );

    $pdo->exec(
        'ALTER TABLE cars MODIFY category ENUM(' . carsEnumSql(carsCategoryList()) . ") NOT NULL DEFAULT 'Седаны'"
    );

    ensureCarsFuelTypeSchema($pdo);
}

function ensureCarsFuelTypeSchema(PDO $pdo): void
{
    if (!carsHasColumn($pdo, 'fuel_type')) {
        $pdo->exec("ALTER TABLE cars ADD COLUMN fuel_type VARCHAR(32) NULL AFTER drive_type");
    }

    $pdo->exec('ALTER TABLE cars MODIFY fuel_type VARCHAR(32) NULL');

    $pdo->exec(
        "UPDATE cars
         SET fuel_type = CASE
            WHEN fuel_type IS NULL OR TRIM(fuel_type) = '' THEN 'Бензин'
            WHEN LOWER(TRIM(fuel_type)) IN ('бензин', 'gasoline', 'petrol') THEN 'Бензин'
            WHEN LOWER(TRIM(fuel_type)) IN ('гбо', 'lpg', 'cng') THEN 'ГБО'
            WHEN LOWER(TRIM(fuel_type)) IN ('дизель', 'diesel') THEN 'Дизель'
            ELSE 'Бензин'
         END"
    );

    $pdo->exec(
        'ALTER TABLE cars MODIFY fuel_type ENUM(' . carsEnumSql(carsFuelTypeList()) . ") NOT NULL DEFAULT 'Бензин'"
    );
}

function carCategoryEmoji(string $category): string
{
    $map = [
        'Премиум' => '✨',
        'Внедорожники' => '🏔️',
        'Бизнес' => '💼',
        'Седаны' => '🚗',
        'Минивэны' => '🚐',
    ];

    return $map[$category] ?? '🚘';
}

function isCarRented(array $car): bool
{
    return (int) ($car['rent_active'] ?? 0) === 1;
}

function carMinutesLeft(array $car): int
{
    if (!isCarRented($car) || empty($car['rent_end_at'])) {
        return 0;
    }

    $rentEndTs = strtotime((string) $car['rent_end_at']);

    if ($rentEndTs === false) {
        return 0;
    }

    return (int) max(0, floor(($rentEndTs - time()) / 60));
}

function formatRentEnd(?string $rentEndAt): string
{
    if ($rentEndAt === null || $rentEndAt === '') {
        return 'не указано';
    }

    $rentEndTs = strtotime($rentEndAt);

    if ($rentEndTs === false) {
        return 'не указано';
    }

    return date('d.m H:i', $rentEndTs);
}

function carsCategoryList(): array
{
    return ['Премиум', 'Внедорожники', 'Бизнес', 'Седаны', 'Минивэны'];
}

function carsDriveTypeList(): array
{
    return ['Передний', 'Задний', 'Полный'];
}

function carsFuelTypeList(): array
{
    return ['Бензин', 'ГБО', 'Дизель'];
}

function fetchCarsForAdmin(PDO $pdo, ?string $search = null, ?string $category = null): array
{
    $sql = 'SELECT id, category, name, description, engine_volume, drive_type, fuel_type, price_per_day, image_url, is_active, created_at, updated_at
            FROM cars
            WHERE 1 = 1';

    $params = [];

    if ($search !== null && $search !== '') {
        $sql .= ' AND (name LIKE :search_name OR description LIKE :search_description)';
        $params['search_name'] = '%' . $search . '%';
        $params['search_description'] = '%' . $search . '%';
    }

    if ($category !== null && $category !== '') {
        $sql .= ' AND category = :category';
        $params['category'] = $category;
    }

    $sql .= ' ORDER BY id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function findCarById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT id, category, name, description, engine_volume, drive_type, fuel_type, price_per_day, image_url, is_active, created_at, updated_at
         FROM cars
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute(['id' => $id]);
    $car = $stmt->fetch();

    return is_array($car) ? $car : null;
}

function createCar(PDO $pdo, array $data): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO cars (category, name, description, engine_volume, drive_type, fuel_type, price_per_day, image_url, is_active)
         VALUES (:category, :name, :description, :engine_volume, :drive_type, :fuel_type, :price_per_day, :image_url, :is_active)'
    );

    $stmt->execute([
        'category' => $data['category'],
        'name' => $data['name'],
        'description' => $data['description'],
        'engine_volume' => $data['engine_volume'],
        'drive_type' => $data['drive_type'],
        'fuel_type' => $data['fuel_type'],
        'price_per_day' => $data['price_per_day'],
        'image_url' => $data['image_url'],
        'is_active' => $data['is_active'],
    ]);

    return (int) $pdo->lastInsertId();
}

function updateCar(PDO $pdo, int $id, array $data): void
{
    $stmt = $pdo->prepare(
        'UPDATE cars
         SET category = :category,
             name = :name,
             description = :description,
             engine_volume = :engine_volume,
             drive_type = :drive_type,
             fuel_type = :fuel_type,
             price_per_day = :price_per_day,
             image_url = :image_url,
             is_active = :is_active
         WHERE id = :id'
    );

    $stmt->execute([
        'id' => $id,
        'category' => $data['category'],
        'name' => $data['name'],
        'description' => $data['description'],
        'engine_volume' => $data['engine_volume'],
        'drive_type' => $data['drive_type'],
        'fuel_type' => $data['fuel_type'],
        'price_per_day' => $data['price_per_day'],
        'image_url' => $data['image_url'],
        'is_active' => $data['is_active'],
    ]);
}

function deleteCarById(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('DELETE FROM cars WHERE id = :id');
    $stmt->execute(['id' => $id]);
}
