<?php
/**
 * Logout Handler
 */
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua variabel sesi
$_SESSION = [];

// Hapus session cookie jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Mulai sesi baru hanya untuk flash message logout
session_start();
$_SESSION['flash_success'] = 'Anda telah berhasil keluar dari sistem.';

header("Location: " . BASE_URL . "/auth/login.php");
exit;
