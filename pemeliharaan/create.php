<?php
/**
 * Tambah / Jadwalkan Pemeliharaan Baru
 */
$page_title = 'Catat Pemeliharaan Alat';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$error = '';
$kode_otomatis = generate_kode_pemeliharaan($pdo);
$id_alat_selected = (int)($_GET['id_alat'] ?? 0);

// Ambil semua daftar alat untuk dropdown
$daftar_alat = $pdo->query("SELECT id, kode_alat, nama_alat, merek, lokasi FROM alat ORDER BY nama_alat ASC")->fetchAll();

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
            // Cek duplikasi kode pemeliharaan
            $stmt = $pdo->prepare("SELECT id FROM pemeliharaan WHERE kode_pemeliharaan = ?");
            $stmt->execute([$kode_pemeliharaan]);
            if ($stmt->fetch()) {
                $error = "Kode pemeliharaan '{$kode_pemeliharaan}' sudah terdaftar dalam sistem.";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO pemeliharaan (kode_pemeliharaan, id_alat, jenis_pemeliharaan, tanggal_jadwal, tanggal_selesai, teknisi, keluhan, tindakan, suku_cadang, biaya, status, catatan)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $kode_pemeliharaan, $id_alat, $jenis_pemeliharaan, $tanggal_jadwal, $tanggal_selesai, $teknisi, $keluhan, $tindakan, $suku_cadang, $biaya, $status, $catatan
                ]);

                // Sinkronisasi status operasional alat otomatis
                if ($status === 'Sedang Dikerjakan') {
                    $up_alat = $pdo->prepare("UPDATE alat SET status_operasional = 'Dalam Pemeliharaan' WHERE id = ?");
                    $up_alat->execute([$id_alat]);
                } elseif ($status === 'Selesai') {
                    $up_alat = $pdo->prepare("UPDATE alat SET status_operasional = 'Siap Pakai', kondisi = 'Baik' WHERE id = ?");
                    $up_alat->execute([$id_alat]);
                }

                set_flash('success', "Catatan pemeliharaan '{$kode_pemeliharaan}' berhasil disimpan!");
                header("Location: " . BASE_URL . "/pemeliharaan/index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan data pemeliharaan: ' . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Pencatatan Tugas Pemeliharaan</h4>
        <p class="text-muted small mb-0">Input jadwal servis rutin, penanganan keluhan perbaikan, atau inspeksi elektromedis.</p>
    </div>
    <a href="<?= BASE_URL; ?>/pemeliharaan/index.php" class="btn btn-light border">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center rounded-3 p-3 mb-4 shadow-sm">
        <i class="fas fa-circle-exclamation fs-5 me-3 text-danger"></i>
        <div class="small fw-medium"><?= htmlspecialchars($error); ?></div>
    </div>
<?php endif; ?>

<div class="card-custom">
    <div class="card-header">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-pen-to-square me-2 text-warning"></i>Formulir Log Servis &amp; Perawatan</h6>
    </div>
    <div class="card-body">
        <form action="" method="POST">
            <?= csrf_field(); ?>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kode Pemeliharaan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control font-monospace fw-bold text-primary" name="kode_pemeliharaan" value="<?= htmlspecialchars($_POST['kode_pemeliharaan'] ?? $kode_otomatis); ?>" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Pilih Alat Elektromedis <span class="text-danger">*</span></label>
                    <select class="form-select" name="id_alat" required>
                        <option value="">-- Pilih Alat --</option>
                        <?php foreach ($daftar_alat as $alt): ?>
                            <option value="<?= $alt['id']; ?>" <?= ($id_alat_selected == $alt['id'] || (($_POST['id_alat'] ?? 0) == $alt['id'])) ? 'selected' : ''; ?>>
                                [<?= htmlspecialchars($alt['kode_alat']); ?>] <?= htmlspecialchars($alt['nama_alat']); ?> - <?= htmlspecialchars($alt['lokasi']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Jenis Pemeliharaan <span class="text-danger">*</span></label>
                    <select class="form-select" name="jenis_pemeliharaan" required>
                        <option value="Preventif">Preventif (Rutin)</option>
                        <option value="Korektif">Korektif (Perbaikan)</option>
                        <option value="Inspeksi Rutin">Inspeksi Rutin</option>
                        <option value="Darurat">Darurat (Emergency)</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tanggal Rencana / Jadwal <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_jadwal" value="<?= htmlspecialchars($_POST['tanggal_jadwal'] ?? date('Y-m-d')); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tanggal Selesai (Jika Selesai)</label>
                    <input type="date" class="form-control" name="tanggal_selesai" value="<?= htmlspecialchars($_POST['tanggal_selesai'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Teknisi Pelaksana / Penanggung Jawab <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="teknisi" placeholder="Nama teknisi / staf" value="<?= htmlspecialchars($_POST['teknisi'] ?? $user['nama_lengkap']); ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Keluhan / Gejala Kerusakan / Target Servis</label>
                    <textarea class="form-control" name="keluhan" rows="3" placeholder="Deskripsikan keluhan pengguna atau poin-poin yang perlu diperiksa..."><?= htmlspecialchars($_POST['keluhan'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Tindakan Perbaikan / Langkah Servis</label>
                    <textarea class="form-control" name="tindakan" rows="3" placeholder="Pembersihan sensor, pengujian kelistrikan, pelumasan mekanik, dll..."><?= htmlspecialchars($_POST['tindakan'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Suku Cadang Diganti (Spare Parts)</label>
                    <input type="text" class="form-control" name="suku_cadang" placeholder="Contoh: Baterai Li-ion / Kabel Lead" value="<?= htmlspecialchars($_POST['suku_cadang'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Total Biaya Servis &amp; Part (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">Rp</span>
                        <input type="number" class="form-control" name="biaya" placeholder="0" value="<?= htmlspecialchars($_POST['biaya'] ?? '0'); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Status Pengerjaan <span class="text-danger">*</span></label>
                    <select class="form-select" name="status" required>
                        <option value="Terjadwal">Terjadwal</option>
                        <option value="Sedang Dikerjakan">Sedang Dikerjakan</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Dibatalkan">Dibatalkan</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold">Catatan Tambahan &amp; Rekomendasi Teknisi</label>
                <textarea class="form-control" name="catatan" rows="2" placeholder="Catatan ke pengguna ruang, rekomendasi kalibrasi ulang, atau masa pakai part..."><?= htmlspecialchars($_POST['catatan'] ?? ''); ?></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= BASE_URL; ?>/pemeliharaan/index.php" class="btn btn-light px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Simpan Catatan Pemeliharaan
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
