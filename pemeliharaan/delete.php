<?php
/**
 * Hapus Catatan Pemeliharaan
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helper.php';

require_login();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT kode_pemeliharaan FROM pemeliharaan WHERE id = ?");
        $stmt->execute([$id]);
        $maint = $stmt->fetch();

        if ($maint) {
            $del = $pdo->prepare("DELETE FROM pemeliharaan WHERE id = ?");
            $del->execute([$id]);
            set_flash('success', "Catatan pemeliharaan '{$maint['kode_pemeliharaan']}' berhasil dihapus.");
        } else {
            set_flash('error', "Data pemeliharaan tidak ditemukan.");
        }
    } catch (PDOException $e) {
        set_flash('error', "Gagal menghapus data: " . $e->getMessage());
    }
}

header("Location: " . BASE_URL . "/pemeliharaan/index.php");
exit;
