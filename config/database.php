<?php
/**
 * Konfigurasi Database & Auto Setup
 * Sistem Manajemen Inventaris, Pemeliharaan & Kalibrasi (SIMRS)
 */

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'simrs_crud';

// Deteksi Base URL otomatis
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

// Cari posisi folder /crud
$base_path = '/crud';
if (preg_match('#^/([^/]+)#', $_SERVER['REQUEST_URI'] ?? '', $matches)) {
    // jika subfolder ditemukan
}
define('BASE_URL', $protocol . $host . '/crud');

// Inisialisasi PDO dengan penanganan auto-create database jika belum ada
try {
    // 1. Coba koneksi langsung ke database simrs_crud
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // 2. Jika database belum ada (error code 1049), buat database otomatis
    try {
        $pdo_root = new PDO("mysql:host={$db_host};charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo_root->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        // Baca file database.sql jika ada untuk inisialisasi tabel
        $sql_file = __DIR__ . '/../database.sql';
        if (file_exists($sql_file)) {
            $sql_content = file_get_contents($sql_file);
            $pdo->exec($sql_content);
        }
    } catch (PDOException $ex) {
        die("Koneksi Database Gagal: " . htmlspecialchars($ex->getMessage()));
    }
}

// Inisialisasi MySQLi untuk kompatibilitas jika dibutuhkan
$mysqli = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$mysqli) {
    // fallback attempt
    $mysqli = mysqli_init();
}
