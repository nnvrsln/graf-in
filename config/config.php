<?php

declare(strict_types=1);

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'invest',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: 'root',
        'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
    ],
    'contacts' => [
        'address' => 'г. Махачкала, подача по Дагестану и ближайшему региону',
        'address_short' => 'Выдача и подача по городу',
        'phone' => '+7 (999) 123-45-67',
        'phone_href' => '+79991234567',
        'telegram' => '@drift_rent',
        'telegram_link' => 'https://t.me/drift_rent',
        'whatsapp' => '+7 (999) 123-45-67',
        'whatsapp_link' => 'https://wa.me/79991234567',
        'work_time' => 'Ежедневно: 09:00 - 23:00',
        'contact_note' => 'Работаем ежедневно. Подтверждаем бронь, показываем доступные модели и ориентируем по времени выдачи.',
    ],
    'booking_destination' => 'whatsapp',
    'widgets' => [
        'yandex_reviews_src' => 'https://yandex.ru/maps-reviews-widget/170480427709?comments',
    ],
];