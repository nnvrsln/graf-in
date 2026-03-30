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

function carCategoryEmoji(string $category): string
{
    $map = [
        'Внедорожник' => '🚙',
        'Седан' => '🚗',
        'Пикап' => '🛻',
        'Кроссовер' => '🚘',
        'Купе' => '🏎️',
        'Хэтчбек' => '🚗',
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