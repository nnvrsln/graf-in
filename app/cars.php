<?php

declare(strict_types=1);

function fetchCars(PDO $pdo, ?string $category = null, int $limit = 50): array
{
    $limit = max(1, min($limit, 200));

    $sql = 'SELECT id, category, name, description, engine_volume, drive_type, price_per_day, image_url, is_rented, rent_end_at,
                   CASE WHEN is_rented = 1 AND rent_end_at IS NOT NULL AND rent_end_at > NOW() THEN 1 ELSE 0 END AS rent_active
            FROM cars
            WHERE is_active = 1';

    $params = [];

    if ($category !== null && $category !== '') {
        $sql .= ' AND category = :category';
        $params['category'] = $category;
    }

    $sql .= ' ORDER BY rent_active DESC, rent_end_at ASC, id DESC LIMIT ' . $limit;

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
        'SELECT id, category, name, description, engine_volume, drive_type, price_per_day, image_url
         FROM cars
         WHERE is_active = 1
         ORDER BY id DESC'
    )->fetchAll();

    if ($cars === []) {
        return [];
    }

    $categoryOrder = ['Премиум', 'Внедорожники', 'Бизнес', 'Седаны', 'Минивэны'];
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

function ensureCarsCategorySchema(PDO $pdo): void
{
    $categoryColumn = $pdo->query("SHOW COLUMNS FROM cars LIKE 'category'")->fetch();
    if (!$categoryColumn) {
        return;
    }

    $hasCarClass = carsHasColumn($pdo, 'car_class');

    $legacyCategories = [
        'Внедорожник', 'Седан', 'Пикап', 'Кроссовер', 'Купе', 'Хэтчбек', 'Минивэн',
        'Р’РЅРµРґРѕСЂРѕР¶РЅРёРє', 'РЎРµРґР°РЅ', 'РџРёРєР°Рї', 'РљСЂРѕСЃСЃРѕРІРµСЂ', 'РљСѓРїРµ', 'РҐСЌС‚С‡Р±РµРє', 'РњРёРЅРёРІСЌРЅ',
    ];
    $targetCategories = [
        'Премиум', 'Внедорожники', 'Бизнес', 'Седаны', 'Минивэны',
        'РџСЂРµРјРёСѓРј', 'Р’РЅРµРґРѕСЂРѕР¶РЅРёРєРё', 'Р‘РёР·РЅРµСЃ', 'РЎРµРґР°РЅС‹', 'РњРёРЅРёРІСЌРЅС‹',
    ];

    $enumForMigration = array_unique(array_merge($targetCategories, $legacyCategories));
    $enumSql = "'" . implode("','", array_map(static function (string $v): string {
        return str_replace("'", "''", $v);
    }, $enumForMigration)) . "'";

    $pdo->exec("ALTER TABLE cars MODIFY category ENUM($enumSql) NOT NULL");

    if ($hasCarClass) {
        $carClassEnumSql = "'" . implode("','", array_map(static function (string $v): string {
            return str_replace("'", "''", $v);
        }, $targetCategories)) . "'";

        $pdo->exec("ALTER TABLE cars MODIFY car_class ENUM($carClassEnumSql) NULL");

        $pdo->exec(
            "UPDATE cars
             SET category = CASE
                WHEN car_class IN ('Премиум', 'РџСЂРµРјРёСѓРј') THEN 'Премиум'
                WHEN car_class IN ('Внедорожники', 'Р’РЅРµРґРѕСЂРѕР¶РЅРёРєРё') THEN 'Внедорожники'
                WHEN car_class IN ('Бизнес', 'Р‘РёР·РЅРµСЃ') THEN 'Бизнес'
                WHEN car_class IN ('Седаны', 'РЎРµРґР°РЅС‹') THEN 'Седаны'
                WHEN car_class IN ('Минивэны', 'РњРёРЅРёРІСЌРЅС‹') THEN 'Минивэны'

                WHEN category IN ('Внедорожник', 'Кроссовер', 'Пикап', 'Р’РЅРµРґРѕСЂРѕР¶РЅРёРє', 'РљСЂРѕСЃСЃРѕРІРµСЂ', 'РџРёРєР°Рї', 'Внедорожники', 'Р’РЅРµРґРѕСЂРѕР¶РЅРёРєРё') THEN 'Внедорожники'
                WHEN category IN ('Купе', 'Премиум', 'РљСѓРїРµ', 'РџСЂРµРјРёСѓРј') THEN 'Премиум'
                WHEN category IN ('Минивэн', 'Минивэны', 'РњРёРЅРёРІСЌРЅ', 'РњРёРЅРёРІСЌРЅС‹') THEN 'Минивэны'
                WHEN category IN ('Бизнес', 'Р‘РёР·РЅРµСЃ') THEN 'Бизнес'
                WHEN category IN ('Седаны', 'РЎРµРґР°РЅС‹') THEN 'Седаны'
                WHEN category IN ('Седан', 'Хэтчбек', 'РЎРµРґР°РЅ', 'РҐСЌС‚С‡Р±РµРє') AND price_per_day >= 15000 THEN 'Премиум'
                WHEN category IN ('Седан', 'Хэтчбек', 'РЎРµРґР°РЅ', 'РҐСЌС‚С‡Р±РµРє') AND price_per_day >= 8500 THEN 'Бизнес'
                WHEN category IN ('Седан', 'Хэтчбек', 'РЎРµРґР°РЅ', 'РҐСЌС‚С‡Р±РµРє') THEN 'Седаны'
                ELSE 'Седаны'
             END"
        );

        $pdo->exec('ALTER TABLE cars DROP COLUMN car_class');
    } else {
        $pdo->exec(
            "UPDATE cars
             SET category = CASE
                WHEN category IN ('Премиум', 'РџСЂРµРјРёСѓРј', 'Купе', 'РљСѓРїРµ') THEN 'Премиум'
                WHEN category IN ('Внедорожники', 'Р’РЅРµРґРѕСЂРѕР¶РЅРёРєРё', 'Внедорожник', 'Кроссовер', 'Пикап', 'Р’РЅРµРґРѕСЂРѕР¶РЅРёРє', 'РљСЂРѕСЃСЃРѕРІРµСЂ', 'РџРёРєР°Рї') THEN 'Внедорожники'
                WHEN category IN ('Бизнес', 'Р‘РёР·РЅРµСЃ') THEN 'Бизнес'
                WHEN category IN ('Минивэны', 'Минивэн', 'РњРёРЅРёРІСЌРЅС‹', 'РњРёРЅРёРІСЌРЅ') THEN 'Минивэны'
                WHEN category IN ('Седаны', 'РЎРµРґР°РЅС‹') THEN 'Седаны'
                WHEN category IN ('Седан', 'Хэтчбек', 'РЎРµРґР°РЅ', 'РҐСЌС‚С‡Р±РµРє') AND price_per_day >= 15000 THEN 'Премиум'
                WHEN category IN ('Седан', 'Хэтчбек', 'РЎРµРґР°РЅ', 'РҐСЌС‚С‡Р±РµРє') AND price_per_day >= 8500 THEN 'Бизнес'
                WHEN category IN ('Седан', 'Хэтчбек', 'РЎРµРґР°РЅ', 'РҐСЌС‚С‡Р±РµРє') THEN 'Седаны'
                ELSE 'Седаны'
             END"
        );
    }

    $pdo->exec(
        "ALTER TABLE cars
         MODIFY category ENUM('Премиум', 'Внедорожники', 'Бизнес', 'Седаны', 'Минивэны') NOT NULL"
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