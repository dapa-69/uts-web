<?php
/**
 * Hapus Data Kalibrasi
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helper.php';

require_login();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT nomor_sertifikat, dokumen FROM kalibrasi WHERE id = ?");
        $stmt->execute([$id]);
        $cal = $stmt->fetch();

        if ($cal) {
            if (!empty($cal['dokumen'])) {
                $doc_path = __DIR__ . '/../assets/uploads/sertifikat/' . $cal['dokumen'];
                if (file_exists($doc_path)) {
                    @unlink($doc_path);
                }
            }

            $del = $pdo->prepare("DELETE FROM kalibrasi WHERE id = ?");
            $del->execute([$id]);

            set_flash('success', "Data kalibrasi '{$cal['nomor_sertifikat']}' berhasil dihapus.");
        } else {
            set_flash('error', "Data kalibrasi tidak ditemukan.");
        }
    } catch (PDOException $e) {
        set_flash('error', "Gagal menghapus data: " . $e->getMessage());
    }
}

header("Location: " . BASE_URL . "/kalibrasi/index.php");
exit;
