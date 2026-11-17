<?php
/**
 * Auth Middleware & Session Security
 */

if (session_status() === PHP_SESSION_NONE) {
    // Pengaturan cookie sesi yang aman
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

require_once __DIR__ . '/database.php';

/**
 * Cek apakah user sedang login
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Proteksi halaman: Redirect ke halaman login jika belum login
 */
function require_login(): void {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = "Silakan masuk terlebih dahulu untuk mengakses sistem.";
        $redirect_to = urlencode($_SERVER['REQUEST_URI']);
        header("Location: " . BASE_URL . "/auth/login.php?redirect=" . $redirect_to);
        exit;
    }
}

/**
 * Ambil data user yang sedang login
 */
function current_user(): ?array {
    global $pdo;
    if (!is_logged_in()) {
        return null;
    }
    
    // Cache di sesi untuk kecepatan
    if (isset($_SESSION['user_data'])) {
        return $_SESSION['user_data'];
    }

    try {
        $stmt = $pdo->prepare("SELECT id, username, nama_lengkap, email, role, foto FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['user_data'] = $user;
            return $user;
        }
    } catch (Exception $e) {
        // Log or handle error
    }
    
    return null;
}

/**
 * Cek otorisasi berdasarkan role (e.g. 'admin', 'teknisi')
 */
function require_role(array|string $roles): void {
    require_login();
    $user = current_user();
    $roles = (array) $roles;
    if (!$user || !in_array($user['role'], $roles)) {
        $_SESSION['flash_error'] = "Anda tidak memiliki hak akses untuk halaman tersebut.";
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}

/**
 * Generate CSRF Token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate hidden input CSRF
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Validasi CSRF Token
 */
function verify_csrf(): bool {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            die("Invalid CSRF Token. Permintaan ditolak demi keamanan.");
        }
    }
    return true;
}
