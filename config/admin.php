<?php

declare(strict_types=1);

return [
    'username' => getenv('ADMIN_USERNAME') ?: 'admin',
    'password_hash' => getenv('ADMIN_PASSWORD_HASH') ?: '$2y$10$uJTHrrQLjuCetquWJoqRw.cZMxnNGpxtG5FbJ6oqoD8qlQhLdCY62',
];
