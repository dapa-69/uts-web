<?php
/**
 * Tambah Alat Inventaris Baru
 */
$page_title = 'Tambah Alat Baru';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$error = '';
$kode_otomatis = generate_kode_alat($pdo);

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
            // Cek duplikasi kode alat
            $stmt = $pdo->prepare("SELECT id FROM alat WHERE kode_alat = ?");
            $stmt->execute([$kode_alat]);
            if ($stmt->fetch()) {
                $error = "Kode alat '{$kode_alat}' sudah terdaftar dalam sistem.";
            } else {
                // Handle upload foto
                $foto_nama = null;
                if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                    if (in_array($ext, $allowed)) {
                        $foto_nama = 'alat_' . time() . '_' . uniqid() . '.' . $ext;
                        $upload_dir = __DIR__ . '/../assets/uploads/alat/';
                        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                        move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $foto_nama);
                    }
                }

                $stmt = $pdo->prepare("
                    INSERT INTO alat (kode_alat, nama_alat, kategori, merek, tipe_model, nomor_seri, lokasi, tahun, harga_pengadaan, kondisi, status_operasional, foto, spesifikasi)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $kode_alat, $nama_alat, $kategori, $merek, $tipe_model, $nomor_seri, $lokasi, $tahun, $harga_pengadaan, $kondisi, $status_operasional, $foto_nama, $spesifikasi
                ]);

                set_flash('success', "Alat '{$nama_alat}' berhasil ditambahkan ke inventaris!");
                header("Location: " . BASE_URL . "/inventaris/index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan data alat: ' . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Registrasi Alat Elektromedis Baru</h4>
        <p class="text-muted small mb-0">Masukkan data detail instrumen atau fasilitas baru ke dalam basis data.</p>
    </div>
    <a href="<?= BASE_URL; ?>/inventaris/index.php" class="btn btn-light border">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
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
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-pen-to-square me-2 text-primary"></i>Formulir Data Alat</h6>
    </div>
    <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
            <?= csrf_field(); ?>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kode Alat / Aset <span class="text-danger">*</span></label>
                    <input type="text" class="form-control font-monospace fw-bold text-primary" name="kode_alat" value="<?= htmlspecialchars($_POST['kode_alat'] ?? $kode_otomatis); ?>" required>
                    <small class="text-muted" style="font-size: 0.72rem;">Nomor identifikasi unik alat</small>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Nama Alat Medis <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_alat" placeholder="Contoh: Patient Monitor Multi Parameter" value="<?= htmlspecialchars($_POST['nama_alat'] ?? ''); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Kategori Alat <span class="text-danger">*</span></label>
                    <select class="form-select" name="kategori" required>
                        <option value="Elektromedis Life Support">Elektromedis Life Support</option>
                        <option value="Elektromedis Terapi">Elektromedis Terapi</option>
                        <option value="Elektromedis Diagnostik">Elektromedis Diagnostik</option>
                        <option value="Radiologi">Radiologi &amp; Pencitraan</option>
                        <option value="Laboratorium & Bedah">Laboratorium &amp; Bedah</option>
                        <option value="Umum & Penunjang">Umum &amp; Penunjang Medis</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Merek / Pabrikan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="merek" placeholder="Contoh: Philips / Mindray / GE" value="<?= htmlspecialchars($_POST['merek'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tipe / Model</label>
                    <input type="text" class="form-control" name="tipe_model" placeholder="Contoh: uMEC 10 / HeartStart" value="<?= htmlspecialchars($_POST['tipe_model'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Nomor Seri (Serial Number)</label>
                    <input type="text" class="form-control font-monospace" name="nomor_seri" placeholder="SN-XXXX-XXXX" value="<?= htmlspecialchars($_POST['nomor_seri'] ?? ''); ?>">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Lokasi Penempatan / Ruangan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="lokasi" list="listLokasi" placeholder="Contoh: Ruang ICU Bed 1" value="<?= htmlspecialchars($_POST['lokasi'] ?? ''); ?>" required>
                    <datalist id="listLokasi">
                        <option value="Ruang ICU Bed 1">
                        <option value="Ruang ICU Bed 2">
                        <option value="Ruang IGD">
                        <option value="Ruang Rawat Inap Melati">
                        <option value="Ruang Radiologi">
                        <option value="Kamar Bedah Sentral (OK)">
                        <option value="Poli Jantung">
                        <option value="Poli Kebidanan & Kandungan">
                        <option value="Laboratorium Sentral">
                        <option value="CSSD Sterilisasi">
                    </datalist>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tahun Pengadaan</label>
                    <input type="number" class="form-control" name="tahun" min="1990" max="<?= date('Y') + 1; ?>" value="<?= htmlspecialchars($_POST['tahun'] ?? date('Y')); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Harga / Nilai Pengadaan (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">Rp</span>
                        <input type="number" class="form-control" name="harga_pengadaan" placeholder="0" value="<?= htmlspecialchars($_POST['harga_pengadaan'] ?? '0'); ?>">
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kondisi Fisik Alat</label>
                    <select class="form-select" name="kondisi">
                        <option value="Baik" <?= (($_POST['kondisi'] ?? '') === 'Baik') ? 'selected' : ''; ?>>Baik</option>
                        <option value="Rusak Ringan" <?= (($_POST['kondisi'] ?? '') === 'Rusak Ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                        <option value="Rusak Berat" <?= (($_POST['kondisi'] ?? '') === 'Rusak Berat') ? 'selected' : ''; ?>>Rusak Berat</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Status Operasional</label>
                    <select class="form-select" name="status_operasional">
                        <option value="Siap Pakai" <?= (($_POST['status_operasional'] ?? '') === 'Siap Pakai') ? 'selected' : ''; ?>>Siap Pakai</option>
                        <option value="Dalam Pemeliharaan" <?= (($_POST['status_operasional'] ?? '') === 'Dalam Pemeliharaan') ? 'selected' : ''; ?>>Dalam Pemeliharaan</option>
                        <option value="Dalam Kalibrasi" <?= (($_POST['status_operasional'] ?? '') === 'Dalam Kalibrasi') ? 'selected' : ''; ?>>Dalam Kalibrasi</option>
                        <option value="Rusak" <?= (($_POST['status_operasional'] ?? '') === 'Rusak') ? 'selected' : ''; ?>>Rusak</option>
                        <option value="Afkir" <?= (($_POST['status_operasional'] ?? '') === 'Afkir') ? 'selected' : ''; ?>>Afkir</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Foto Alat (Opsional)</label>
                    <input type="file" class="form-control" name="foto" accept="image/*">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold">Spesifikasi Teknis / Catatan Fasilitas</label>
                <textarea class="form-control" name="spesifikasi" rows="3" placeholder="Tuliskan spesifikasi teknis, nomor izin edar, aksesoris bawaan, atau catatan penting lainnya..."><?= htmlspecialchars($_POST['spesifikasi'] ?? ''); ?></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= BASE_URL; ?>/inventaris/index.php" class="btn btn-light px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Simpan Data Alat
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
