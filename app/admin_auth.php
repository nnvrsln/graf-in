<?php

declare(strict_types=1);

function adminBootSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('invest_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function adminConfig(): array
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $config = [
        'username' => 'admin',
        'password_hash' => '$2y$10$uJTHrrQLjuCetquWJoqRw.cZMxnNGpxtG5FbJ6oqoD8qlQhLdCY62',
    ];

    $configFile = __DIR__ . '/../config/admin.php';
    if (is_file($configFile)) {
        $loaded = require $configFile;
        if (is_array($loaded)) {
            if (isset($loaded['username']) && is_string($loaded['username']) && $loaded['username'] !== '') {
                $config['username'] = $loaded['username'];
            }

            if (isset($loaded['password_hash']) && is_string($loaded['password_hash']) && $loaded['password_hash'] !== '') {
                $config['password_hash'] = $loaded['password_hash'];
            }
        }
    }

    return $config;
}

function adminIsAuthenticated(): bool
{
    adminBootSession();

    return isset($_SESSION['admin_user']) && is_string($_SESSION['admin_user']) && $_SESSION['admin_user'] !== '';
}

function adminAttemptLogin(string $username, string $password): bool
{
    adminBootSession();

    $username = trim($username);
    $config = adminConfig();

    $validUser = hash_equals((string) $config['username'], $username);
    $validPassword = password_verify($password, (string) $config['password_hash']);

    if (!$validUser || !$validPassword) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_user'] = $config['username'];

    return true;
}

function adminRequireAuth(): void
{
    if (adminIsAuthenticated()) {
        return;
    }

    header('Location: /admin/login.php');
    exit;
}

function adminLogout(): void
{
    adminBootSession();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
}

function adminCsrfToken(): string
{
    adminBootSession();

    if (!isset($_SESSION['admin_csrf']) || !is_string($_SESSION['admin_csrf']) || $_SESSION['admin_csrf'] === '') {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['admin_csrf'];
}

function adminVerifyCsrf(?string $token): bool
{
    if ($token === null || $token === '') {
        return false;
    }

    $known = adminCsrfToken();

    return hash_equals($known, $token);
}

function adminFlashSet(string $type, string $message): void
{
    adminBootSession();

    $_SESSION['admin_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function adminFlashPull(): ?array
{
    adminBootSession();

    if (!isset($_SESSION['admin_flash']) || !is_array($_SESSION['admin_flash'])) {
        return null;
    }

    $flash = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);

    return $flash;
}