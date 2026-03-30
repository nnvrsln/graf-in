<?php

declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
require __DIR__ . '/../app/database.php';

$pdo = dbConnect($config['db']);

$pdo->exec('DROP TABLE IF EXISTS cars');

$pdo->exec(
    "CREATE TABLE cars (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        category ENUM('Внедорожник', 'Седан', 'Пикап', 'Кроссовер', 'Купе', 'Хэтчбек') NOT NULL,
        name VARCHAR(120) NOT NULL,
        description TEXT NOT NULL,
        engine_volume DECIMAL(3,1) NOT NULL,
        drive_type ENUM('Передний', 'Задний', 'Полный') NOT NULL,
        price_per_day INT UNSIGNED NOT NULL,
        image_url VARCHAR(700) NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        is_rented TINYINT(1) NOT NULL DEFAULT 0,
        rent_end_at DATETIME NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_cars_category (category),
        KEY idx_cars_active (is_active),
        KEY idx_cars_rent (is_rented, rent_end_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

$rentFast = date('Y-m-d H:i:s', strtotime('+6 hours'));
$rentDay = date('Y-m-d H:i:s', strtotime('+1 day 4 hours'));
$rentLong = date('Y-m-d H:i:s', strtotime('+3 days 2 hours'));

$cars = [
    ['Внедорожник', 'Toyota Land Cruiser 300', 'Максимальный комфорт для дальних поездок по трассе и горным маршрутам.', 3.5, 'Полный', 16500, 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1200&q=80', 1, $rentDay],
    ['Седан', 'Toyota Camry', 'Универсальный седан для города и междугородних поездок с мягким ходом.', 2.5, 'Передний', 6500, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80', 0, null],
    ['Пикап', 'Ford F-150', 'Надежный пикап для активного отдыха, перевозки снаряжения и выездов за город.', 3.5, 'Полный', 14500, 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=80', 1, $rentFast],
    ['Седан', 'Hyundai Elantra', 'Экономичный и комфортный автомобиль для ежедневных маршрутов.', 2.0, 'Передний', 5200, 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=80', 0, null],
    ['Кроссовер', 'BMW X5', 'Премиальный кроссовер с отличной динамикой и высоким уровнем безопасности.', 3.0, 'Полный', 14000, 'https://images.unsplash.com/photo-1502877338535-766e1452684a?auto=format&fit=crop&w=1200&q=80', 1, $rentLong],
    ['Купе', 'Mercedes-Benz C Coupe', 'Стильное купе для тех, кто ценит драйв и выразительный внешний вид.', 2.0, 'Задний', 11000, 'https://images.unsplash.com/photo-1549399542-7e82138f1a26?auto=format&fit=crop&w=1200&q=80', 0, null],
    ['Хэтчбек', 'Volkswagen Golf', 'Практичный компактный хэтчбек с удобной посадкой и маневренностью.', 1.4, 'Передний', 5800, 'https://images.unsplash.com/photo-1489824904134-891ab64532f1?auto=format&fit=crop&w=1200&q=80', 0, null],
    ['Внедорожник', 'Mitsubishi Pajero Sport', 'Рамный внедорожник для уверенного движения по сложным дорогам.', 2.4, 'Полный', 11800, 'https://images.unsplash.com/photo-1542362567-b07e54358753?auto=format&fit=crop&w=1200&q=80', 0, null],
    ['Седан', 'Porsche Panamera', 'Быстрый и статусный седан для деловых поездок и особых случаев.', 2.9, 'Полный', 18500, 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1200&q=80', 0, null],
];

$stmt = $pdo->prepare(
    'INSERT INTO cars (category, name, description, engine_volume, drive_type, price_per_day, image_url, is_rented, rent_end_at)
     VALUES (:category, :name, :description, :engine_volume, :drive_type, :price_per_day, :image_url, :is_rented, :rent_end_at)'
);

foreach ($cars as $car) {
    $stmt->execute([
        'category' => $car[0],
        'name' => $car[1],
        'description' => $car[2],
        'engine_volume' => $car[3],
        'drive_type' => $car[4],
        'price_per_day' => $car[5],
        'image_url' => $car[6],
        'is_rented' => $car[7],
        'rent_end_at' => $car[8],
    ]);
}

echo 'Cars seeded: ' . count($cars) . PHP_EOL;