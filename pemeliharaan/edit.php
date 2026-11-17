<?php
/**
 * Edit / Update Pemeliharaan
 */
$page_title = 'Update Pemeliharaan';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM pemeliharaan WHERE id = ?");
$stmt->execute([$id]);
$maint = $stmt->fetch();

if (!$maint) {
    set_flash('error', 'Catatan pemeliharaan tidak ditemukan.');
    header("Location: " . BASE_URL . "/pemeliharaan/index.php");
    exit;
}

// Ambil semua alat untuk dropdown
$daftar_alat = $pdo->query("SELECT id, kode_alat, nama_alat, merek, lokasi FROM alat ORDER BY nama_alat ASC")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $kode_pemeliharaan = trim($_POST['kode_pemeliharaan'] ?? '');
    $id_alat = (int)($_POST['id_alat'] ?? 0);
    $jenis_pemeliharaan = $_POST['jenis_pemeliharaan'] ?? 'Preventif';
    $tanggal_jadwal = $_POST['tanggal_jadwal'] ?? date('Y-m-d');
    $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
    $teknisi = trim($_POST['teknisi'] ?? '');
    $keluhan = trim($_POST['keluhan'] ?? '');
    $tindakan = trim($_POST['tindakan'] ?? '');
    $suku_cadang = trim($_POST['suku_cadang'] ?? '');
    $biaya = (float)str_replace(['Rp', '.', ' ', ','], ['', '', '', '.'], $_POST['biaya'] ?? '0');
    $status = $_POST['status'] ?? 'Terjadwal';
    $catatan = trim($_POST['catatan'] ?? '');

    if (empty($kode_pemeliharaan) || empty($id_alat) || empty($teknisi) || empty($tanggal_jadwal)) {
        $error = 'Bidang Kode Pemeliharaan, Alat, Teknisi, dan Tanggal Jadwal wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM pemeliharaan WHERE kode_pemeliharaan = ? AND id != ?");
            $stmt->execute([$kode_pemeliharaan, $id]);
            if ($stmt->fetch()) {
                $error = "Kode pemeliharaan '{$kode_pemeliharaan}' sudah digunakan pada data lain.";
            } else {
                $stmt = $pdo->prepare("
                    UPDATE pemeliharaan SET
                        kode_pemeliharaan = ?, id_alat = ?, jenis_pemeliharaan = ?, 
                        tanggal_jadwal = ?, tanggal_selesai = ?, teknisi = ?, 
                        keluhan = ?, tindakan = ?, suku_cadang = ?, biaya = ?, 
                        status = ?, catatan = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $kode_pemeliharaan, $id_alat, $jenis_pemeliharaan, 
                    $tanggal_jadwal, $tanggal_selesai, $teknisi, 
                    $keluhan, $tindakan, $suku_cadang, $biaya, 
                    $status, $catatan, $id
                ]);

                // Sinkronisasi status alat jika selesai
                if ($status === 'Selesai') {
                    $up_alat = $pdo->prepare("UPDATE alat SET status_operasional = 'Siap Pakai', kondisi = 'Baik' WHERE id = ?");
                    $up_alat->execute([$id_alat]);
                } elseif ($status === 'Sedang Dikerjakan') {
                    $up_alat = $pdo->prepare("UPDATE alat SET status_operasional = 'Dalam Pemeliharaan' WHERE id = ?");
                    $up_alat->execute([$id_alat]);
                }

                set_flash('success', "Data pemeliharaan '{$kode_pemeliharaan}' berhasil diperbarui!");
                header("Location: " . BASE_URL . "/pemeliharaan/detail.php?id=" . $id);
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui data pemeliharaan: ' . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Update Pemeliharaan: <?= htmlspecialchars($maint['kode_pemeliharaan']); ?></h4>
        <p class="text-muted small mb-0">Perbarui progres penanganan servis, tindakan teknisi, suku cadang, dan status akhir.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL; ?>/pemeliharaan/detail.php?id=<?= $id; ?>" class="btn btn-light border">
            <i class="fas fa-eye me-1"></i> Lihat BAP
        </a>
        <a href="<?= BASE_URL; ?>/pemeliharaan/index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center rounded-3 p-3 mb-4 shadow-sm">
        <i class="fas fa-circle-exclamation fs-5 me-3 text-danger"></i>
        <div class="small fw-medium"><?= htmlspecialchars($error); ?></div>
    </div>
<?php endif; ?>

<div class="card-custom">
    <div class="card-header">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-pen-to-square me-2 text-warning"></i>Formulir Perubahan Log Servis</h6>
    </div>
    <div class="card-body">
        <form action="" method="POST">
            <?= csrf_field(); ?>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kode Pemeliharaan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control font-monospace fw-bold text-primary" name="kode_pemeliharaan" value="<?= htmlspecialchars($_POST['kode_pemeliharaan'] ?? $maint['kode_pemeliharaan']); ?>" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Alat Elektromedis <span class="text-danger">*</span></label>
                    <select class="form-select" name="id_alat" required>
                        <?php foreach ($daftar_alat as $alt): ?>
                            <option value="<?= $alt['id']; ?>" <?= ($maint['id_alat'] == $alt['id']) ? 'selected' : ''; ?>>
                                [<?= htmlspecialchars($alt['kode_alat']); ?>] <?= htmlspecialchars($alt['nama_alat']); ?> - <?= htmlspecialchars($alt['lokasi']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Jenis Pemeliharaan <span class="text-danger">*</span></label>
                    <?php $jns = $_POST['jenis_pemeliharaan'] ?? $maint['jenis_pemeliharaan']; ?>
                    <select class="form-select" name="jenis_pemeliharaan" required>
                        <option value="Preventif" <?= ($jns == 'Preventif') ? 'selected' : ''; ?>>Preventif (Rutin)</option>
                        <option value="Korektif" <?= ($jns == 'Korektif') ? 'selected' : ''; ?>>Korektif (Perbaikan)</option>
                        <option value="Inspeksi Rutin" <?= ($jns == 'Inspeksi Rutin') ? 'selected' : ''; ?>>Inspeksi Rutin</option>
                        <option value="Darurat" <?= ($jns == 'Darurat') ? 'selected' : ''; ?>>Darurat (Emergency)</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tanggal Rencana / Jadwal <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_jadwal" value="<?= htmlspecialchars($_POST['tanggal_jadwal'] ?? $maint['tanggal_jadwal']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tanggal Selesai</label>
                    <input type="date" class="form-control" name="tanggal_selesai" value="<?= htmlspecialchars($_POST['tanggal_selesai'] ?? $maint['tanggal_selesai'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Teknisi Pelaksana <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="teknisi" value="<?= htmlspecialchars($_POST['teknisi'] ?? $maint['teknisi']); ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Keluhan / Gejala Kerusakan Awal</label>
                    <textarea class="form-control" name="keluhan" rows="3"><?= htmlspecialchars($_POST['keluhan'] ?? $maint['keluhan']); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Tindakan Perbaikan / Langkah Servis</label>
                    <textarea class="form-control" name="tindakan" rows="3"><?= htmlspecialchars($_POST['tindakan'] ?? $maint['tindakan']); ?></textarea>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Suku Cadang Diganti</label>
                    <input type="text" class="form-control" name="suku_cadang" value="<?= htmlspecialchars($_POST['suku_cadang'] ?? $maint['suku_cadang']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Total Biaya (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">Rp</span>
                        <input type="number" class="form-control" name="biaya" value="<?= htmlspecialchars($_POST['biaya'] ?? (int)$maint['biaya']); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Status Pengerjaan <span class="text-danger">*</span></label>
                    <?php $sts = $_POST['status'] ?? $maint['status']; ?>
                    <select class="form-select" name="status" required>
                        <option value="Terjadwal" <?= ($sts == 'Terjadwal') ? 'selected' : ''; ?>>Terjadwal</option>
                        <option value="Sedang Dikerjakan" <?= ($sts == 'Sedang Dikerjakan') ? 'selected' : ''; ?>>Sedang Dikerjakan</option>
                        <option value="Selesai" <?= ($sts == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                        <option value="Dibatalkan" <?= ($sts == 'Dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold">Catatan &amp; Rekomendasi Teknisi</label>
                <textarea class="form-control" name="catatan" rows="2"><?= htmlspecialchars($_POST['catatan'] ?? $maint['catatan']); ?></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= BASE_URL; ?>/pemeliharaan/detail.php?id=<?= $id; ?>" class="btn btn-light px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Perbarui Catatan Pemeliharaan
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
