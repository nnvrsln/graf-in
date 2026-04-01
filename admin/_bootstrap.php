<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/site_settings.php';

$config = siteLoadConfig(dirname(__DIR__) . '/config/config.php');

require dirname(__DIR__) . '/app/database.php';
require dirname(__DIR__) . '/app/cars.php';
require dirname(__DIR__) . '/app/admin_auth.php';

$pdo = dbConnect($config['db']);
ensureCarsCategorySchema($pdo);

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('adminFormatPrice')) {
    function adminFormatPrice(int $price): string
    {
        return number_format($price, 0, '.', ' ');
    }
}