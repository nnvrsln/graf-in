<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/admin_auth.php';

adminLogout();
header('Location: /admin/login.php');
exit;