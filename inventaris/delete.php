<?php
/**
 * Hapus Data Alat Inventaris
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helper.php';

require_login();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        // Ambil info foto terlebih dahulu
        $stmt = $pdo->prepare("SELECT nama_alat, foto FROM alat WHERE id = ?");
        $stmt->execute([$id]);
        $alat = $stmt->fetch();

        if ($alat) {
            // Hapus file fisik foto jika ada
            if (!empty($alat['foto'])) {
                $foto_path = __DIR__ . '/../assets/uploads/alat/' . $alat['foto'];
                if (file_exists($foto_path)) {
                    @unlink($foto_path);
                }
            }

            // Hapus record alat (pemeliharaan & kalibrasi terhapus otomatis via CASCADE)
            $del = $pdo->prepare("DELETE FROM alat WHERE id = ?");
            $del->execute([$id]);

            set_flash('success', "Alat '{$alat['nama_alat']}' berhasil dihapus dari inventaris.");
        } else {
            set_flash('error', "Data alat tidak ditemukan.");
        }
    } catch (PDOException $e) {
        set_flash('error', "Gagal menghapus data alat: " . $e->getMessage());
    }
}

header("Location: " . BASE_URL . "/inventaris/index.php");
exit;
