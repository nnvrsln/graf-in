<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/admin_auth.php';
adminRequireAuth();

header('Location: /admin/cars.php');
exit;