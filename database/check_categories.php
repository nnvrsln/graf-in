<?php

declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
require __DIR__ . '/../app/database.php';
require __DIR__ . '/../app/cars.php';

$pdo = dbConnect($config['db']);
ensureCarsCategorySchema($pdo);

$category = $pdo->query("SHOW COLUMNS FROM cars LIKE 'category'")->fetch();
$hasCarClass = carsHasColumn($pdo, 'car_class');
$categories = $pdo->query('SELECT DISTINCT category FROM cars ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);

header('Content-Type: text/plain; charset=UTF-8');

echo "Category type: " . ($category['Type'] ?? 'n/a') . PHP_EOL;
echo "car_class exists: " . ($hasCarClass ? 'yes' : 'no') . PHP_EOL;
echo "Categories in data: " . implode(', ', array_map('strval', $categories)) . PHP_EOL;