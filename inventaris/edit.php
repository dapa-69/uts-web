<?php
/**
 * Edit Alat Inventaris
 */
$page_title = 'Edit Data Alat';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM alat WHERE id = ?");
$stmt->execute([$id]);
$alat = $stmt->fetch();

if (!$alat) {
    set_flash('error', 'Data alat tidak ditemukan.');
    header("Location: " . BASE_URL . "/inventaris/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $kode_alat = trim($_POST['kode_alat'] ?? '');
    $nama_alat = trim($_POST['nama_alat'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $merek = trim($_POST['merek'] ?? '');
    $tipe_model = trim($_POST['tipe_model'] ?? '');
    $nomor_seri = trim($_POST['nomor_seri'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $tahun = (int)($_POST['tahun'] ?? date('Y'));
    $harga_pengadaan = (float)str_replace(['Rp', '.', ' ', ','], ['', '', '', '.'], $_POST['harga_pengadaan'] ?? '0');
    $kondisi = $_POST['kondisi'] ?? 'Baik';
    $status_operasional = $_POST['status_operasional'] ?? 'Siap Pakai';
    $spesifikasi = trim($_POST['spesifikasi'] ?? '');

    if (empty($kode_alat) || empty($nama_alat) || empty($merek) || empty($lokasi)) {
        $error = 'Bidang Kode Alat, Nama Alat, Merek, dan Lokasi wajib diisi.';
    } else {
        try {
            // Cek duplikasi kode alat dengan ID lain
            $stmt = $pdo->prepare("SELECT id FROM alat WHERE kode_alat = ? AND id != ?");
            $stmt->execute([$kode_alat, $id]);
            if ($stmt->fetch()) {
                $error = "Kode alat '{$kode_alat}' sudah digunakan pada alat lain.";
            } else {
                $foto_nama = $alat['foto'];
                if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                    if (in_array($ext, $allowed)) {
                        $foto_nama = 'alat_' . time() . '_' . uniqid() . '.' . $ext;
                        $upload_dir = __DIR__ . '/../assets/uploads/alat/';
                        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                        move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $foto_nama);

                        // Hapus foto lama jika ada
                        if (!empty($alat['foto']) && file_exists($upload_dir . $alat['foto'])) {
                            @unlink($upload_dir . $alat['foto']);
                        }
                    }
                }

                $stmt = $pdo->prepare("
                    UPDATE alat SET 
                        kode_alat = ?, nama_alat = ?, kategori = ?, merek = ?, tipe_model = ?, 
                        nomor_seri = ?, lokasi = ?, tahun = ?, harga_pengadaan = ?, 
                        kondisi = ?, status_operasional = ?, foto = ?, spesifikasi = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $kode_alat, $nama_alat, $kategori, $merek, $tipe_model,
                    $nomor_seri, $lokasi, $tahun, $harga_pengadaan,
                    $kondisi, $status_operasional, $foto_nama, $spesifikasi, $id
                ]);

                set_flash('success', "Data alat '{$nama_alat}' berhasil diperbarui!");
                header("Location: " . BASE_URL . "/inventaris/detail.php?id=" . $id);
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui data alat: ' . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Edit Data Alat: <?= htmlspecialchars($alat['nama_alat']); ?></h4>
        <p class="text-muted small mb-0">Perbarui spesifikasi teknis, status, atau lokasi penempatan alat.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL; ?>/inventaris/detail.php?id=<?= $id; ?>" class="btn btn-light border">
            <i class="fas fa-eye me-1"></i> Lihat Detail
        </a>
        <a href="<?= BASE_URL; ?>/inventaris/index.php" class="btn btn-light border">
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
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-edit me-2 text-warning"></i>Perubahan Data Alat [<?= htmlspecialchars($alat['kode_alat']); ?>]</h6>
    </div>
    <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
            <?= csrf_field(); ?>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kode Alat / Aset <span class="text-danger">*</span></label>
                    <input type="text" class="form-control font-monospace fw-bold text-primary" name="kode_alat" value="<?= htmlspecialchars($_POST['kode_alat'] ?? $alat['kode_alat']); ?>" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Nama Alat Medis <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_alat" value="<?= htmlspecialchars($_POST['nama_alat'] ?? $alat['nama_alat']); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Kategori Alat <span class="text-danger">*</span></label>
                    <?php $kat_curr = $_POST['kategori'] ?? $alat['kategori']; ?>
                    <select class="form-select" name="kategori" required>
                        <option value="Elektromedis Life Support" <?= ($kat_curr == 'Elektromedis Life Support') ? 'selected' : ''; ?>>Elektromedis Life Support</option>
                        <option value="Elektromedis Terapi" <?= ($kat_curr == 'Elektromedis Terapi') ? 'selected' : ''; ?>>Elektromedis Terapi</option>
                        <option value="Elektromedis Diagnostik" <?= ($kat_curr == 'Elektromedis Diagnostik') ? 'selected' : ''; ?>>Elektromedis Diagnostik</option>
                        <option value="Radiologi" <?= ($kat_curr == 'Radiologi') ? 'selected' : ''; ?>>Radiologi &amp; Pencitraan</option>
                        <option value="Laboratorium & Bedah" <?= ($kat_curr == 'Laboratorium & Bedah') ? 'selected' : ''; ?>>Laboratorium &amp; Bedah</option>
                        <option value="Umum & Penunjang" <?= ($kat_curr == 'Umum & Penunjang') ? 'selected' : ''; ?>>Umum &amp; Penunjang Medis</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Merek / Pabrikan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="merek" value="<?= htmlspecialchars($_POST['merek'] ?? $alat['merek']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tipe / Model</label>
                    <input type="text" class="form-control" name="tipe_model" value="<?= htmlspecialchars($_POST['tipe_model'] ?? $alat['tipe_model']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Nomor Seri (Serial Number)</label>
                    <input type="text" class="form-control font-monospace" name="nomor_seri" value="<?= htmlspecialchars($_POST['nomor_seri'] ?? $alat['nomor_seri']); ?>">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Lokasi Penempatan / Ruangan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="lokasi" value="<?= htmlspecialchars($_POST['lokasi'] ?? $alat['lokasi']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tahun Pengadaan</label>
                    <input type="number" class="form-control" name="tahun" min="1990" max="<?= date('Y') + 1; ?>" value="<?= htmlspecialchars($_POST['tahun'] ?? $alat['tahun']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Harga / Nilai Pengadaan (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">Rp</span>
                        <input type="number" class="form-control" name="harga_pengadaan" value="<?= htmlspecialchars($_POST['harga_pengadaan'] ?? (int)$alat['harga_pengadaan']); ?>">
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kondisi Fisik Alat</label>
                    <?php $kondisi_curr = $_POST['kondisi'] ?? $alat['kondisi']; ?>
                    <select class="form-select" name="kondisi">
                        <option value="Baik" <?= ($kondisi_curr === 'Baik') ? 'selected' : ''; ?>>Baik</option>
                        <option value="Rusak Ringan" <?= ($kondisi_curr === 'Rusak Ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                        <option value="Rusak Berat" <?= ($kondisi_curr === 'Rusak Berat') ? 'selected' : ''; ?>>Rusak Berat</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Status Operasional</label>
                    <?php $status_curr = $_POST['status_operasional'] ?? $alat['status_operasional']; ?>
                    <select class="form-select" name="status_operasional">
                        <option value="Siap Pakai" <?= ($status_curr === 'Siap Pakai') ? 'selected' : ''; ?>>Siap Pakai</option>
                        <option value="Dalam Pemeliharaan" <?= ($status_curr === 'Dalam Pemeliharaan') ? 'selected' : ''; ?>>Dalam Pemeliharaan</option>
                        <option value="Dalam Kalibrasi" <?= ($status_curr === 'Dalam Kalibrasi') ? 'selected' : ''; ?>>Dalam Kalibrasi</option>
                        <option value="Rusak" <?= ($status_curr === 'Rusak') ? 'selected' : ''; ?>>Rusak</option>
                        <option value="Afkir" <?= ($status_curr === 'Afkir') ? 'selected' : ''; ?>>Afkir</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Ganti Foto Alat</label>
                    <input type="file" class="form-control" name="foto" accept="image/*">
                    <?php if (!empty($alat['foto'])): ?>
                        <small class="text-success"><i class="fas fa-image me-1"></i> Foto tersimpan: <?= htmlspecialchars($alat['foto']); ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold">Spesifikasi Teknis / Catatan Fasilitas</label>
                <textarea class="form-control" name="spesifikasi" rows="3"><?= htmlspecialchars($_POST['spesifikasi'] ?? $alat['spesifikasi']); ?></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= BASE_URL; ?>/inventaris/detail.php?id=<?= $id; ?>" class="btn btn-light px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Perbarui Data Alat
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
