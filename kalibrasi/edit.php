<?php
/**
 * Edit Data Sertifikat Kalibrasi
 */
$page_title = 'Edit Data Kalibrasi';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM kalibrasi WHERE id = ?");
$stmt->execute([$id]);
$cal = $stmt->fetch();

if (!$cal) {
    set_flash('error', 'Data kalibrasi tidak ditemukan.');
    header("Location: " . BASE_URL . "/kalibrasi/index.php");
    exit;
}

$daftar_alat = $pdo->query("SELECT id, kode_alat, nama_alat, merek, lokasi FROM alat ORDER BY nama_alat ASC")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id_alat = (int)($_POST['id_alat'] ?? 0);
    $nomor_sertifikat = trim($_POST['nomor_sertifikat'] ?? '');
    $lembaga_kalibrasi = trim($_POST['lembaga_kalibrasi'] ?? '');
    $tanggal_kalibrasi = $_POST['tanggal_kalibrasi'] ?? date('Y-m-d');
    $tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'] ?? date('Y-m-d');
    $hasil = $_POST['hasil'] ?? 'Laik Pakai';
    $biaya = (float)str_replace(['Rp', '.', ' ', ','], ['', '', '', '.'], $_POST['biaya'] ?? '0');
    $catatan = trim($_POST['catatan'] ?? '');

    if (empty($id_alat) || empty($nomor_sertifikat) || empty($lembaga_kalibrasi) || empty($tanggal_kalibrasi) || empty($tanggal_kadaluarsa)) {
        $error = 'Semua kolom bertanda bintang (*) wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM kalibrasi WHERE nomor_sertifikat = ? AND id != ?");
            $stmt->execute([$nomor_sertifikat, $id]);
            if ($stmt->fetch()) {
                $error = "Nomor sertifikat '{$nomor_sertifikat}' sudah digunakan pada data lain.";
            } else {
                $dokumen_nama = $cal['dokumen'];
                if (!empty($_FILES['dokumen']['name']) && $_FILES['dokumen']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['dokumen']['name'], PATHINFO_EXTENSION));
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                    if (in_array($ext, $allowed)) {
                        $dokumen_nama = 'sertifikat_' . time() . '_' . uniqid() . '.' . $ext;
                        $upload_dir = __DIR__ . '/../assets/uploads/sertifikat/';
                        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                        move_uploaded_file($_FILES['dokumen']['tmp_name'], $upload_dir . $dokumen_nama);

                        if (!empty($cal['dokumen']) && file_exists($upload_dir . $cal['dokumen'])) {
                            @unlink($upload_dir . $cal['dokumen']);
                        }
                    }
                }

                $stmt = $pdo->prepare("
                    UPDATE kalibrasi SET
                        id_alat = ?, nomor_sertifikat = ?, lembaga_kalibrasi = ?, 
                        tanggal_kalibrasi = ?, tanggal_kadaluarsa = ?, hasil = ?, 
                        biaya = ?, dokumen = ?, catatan = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $id_alat, $nomor_sertifikat, $lembaga_kalibrasi, 
                    $tanggal_kalibrasi, $tanggal_kadaluarsa, $hasil, 
                    $biaya, $dokumen_nama, $catatan, $id
                ]);

                set_flash('success', "Data kalibrasi '{$nomor_sertifikat}' berhasil diperbarui!");
                header("Location: " . BASE_URL . "/kalibrasi/detail.php?id=" . $id);
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui data kalibrasi: ' . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Edit Sertifikat: <?= htmlspecialchars($cal['nomor_sertifikat']); ?></h4>
        <p class="text-muted small mb-0">Perbarui tanggal berlaku sertifikat, lembaga penguji, atau dokumen hasil uji.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL; ?>/kalibrasi/detail.php?id=<?= $id; ?>" class="btn btn-light border">
            <i class="fas fa-eye me-1"></i> Lihat Detail
        </a>
        <a href="<?= BASE_URL; ?>/kalibrasi/index.php" class="btn btn-light border">
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
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-pen-to-square me-2 text-warning"></i>Formulir Perubahan Data Sertifikat Kalibrasi</h6>
    </div>
    <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
            <?= csrf_field(); ?>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Pilih Alat Elektromedis <span class="text-danger">*</span></label>
                    <select class="form-select" name="id_alat" required>
                        <?php foreach ($daftar_alat as $alt): ?>
                            <option value="<?= $alt['id']; ?>" <?= ($cal['id_alat'] == $alt['id']) ? 'selected' : ''; ?>>
                                [<?= htmlspecialchars($alt['kode_alat']); ?>] <?= htmlspecialchars($alt['nama_alat']); ?> - <?= htmlspecialchars($alt['lokasi']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nomor Sertifikat Kalibrasi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control font-monospace fw-bold text-primary" name="nomor_sertifikat" value="<?= htmlspecialchars($_POST['nomor_sertifikat'] ?? $cal['nomor_sertifikat']); ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Lembaga Penguji / Balai Kalibrasi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="lembaga_kalibrasi" value="<?= htmlspecialchars($_POST['lembaga_kalibrasi'] ?? $cal['lembaga_kalibrasi']); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tanggal Kalibrasi <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_kalibrasi" value="<?= htmlspecialchars($_POST['tanggal_kalibrasi'] ?? $cal['tanggal_kalibrasi']); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tanggal Kadaluarsa <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_kadaluarsa" value="<?= htmlspecialchars($_POST['tanggal_kadaluarsa'] ?? $cal['tanggal_kadaluarsa']); ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Hasil Kelaikan Kalibrasi <span class="text-danger">*</span></label>
                    <?php $hsl = $_POST['hasil'] ?? $cal['hasil']; ?>
                    <select class="form-select fw-semibold" name="hasil" required>
                        <option value="Laik Pakai" <?= ($hsl == 'Laik Pakai') ? 'selected' : ''; ?> class="text-success">Laik Pakai (Aman &amp; Lolos Standar)</option>
                        <option value="Tidak Laik Pakai" <?= ($hsl == 'Tidak Laik Pakai') ? 'selected' : ''; ?> class="text-danger">Tidak Laik Pakai (Di Luar Toleransi)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Biaya Pelaksanaan Kalibrasi (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">Rp</span>
                        <input type="number" class="form-control" name="biaya" value="<?= htmlspecialchars($_POST['biaya'] ?? (int)$cal['biaya']); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Ganti File Sertifikat (PDF / Gambar)</label>
                    <input type="file" class="form-control" name="dokumen" accept=".pdf,image/*">
                    <?php if (!empty($cal['dokumen'])): ?>
                        <small class="text-success"><i class="fas fa-file-pdf me-1"></i> Ada dokumen tersimpan</small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold">Catatan Hasil Uji &amp; Parameter</label>
                <textarea class="form-control" name="catatan" rows="3"><?= htmlspecialchars($_POST['catatan'] ?? $cal['catatan']); ?></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= BASE_URL; ?>/kalibrasi/detail.php?id=<?= $id; ?>" class="btn btn-light px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Perbarui Data Kalibrasi
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
