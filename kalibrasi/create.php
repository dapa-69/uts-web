<?php
/**
 * Input Data Kalibrasi Baru
 */
$page_title = 'Input Kalibrasi & Uji Laik';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$error = '';
$id_alat_selected = (int)($_GET['id_alat'] ?? 0);

// Ambil semua alat untuk dropdown
$daftar_alat = $pdo->query("SELECT id, kode_alat, nama_alat, merek, lokasi FROM alat ORDER BY nama_alat ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id_alat = (int)($_POST['id_alat'] ?? 0);
    $nomor_sertifikat = trim($_POST['nomor_sertifikat'] ?? '');
    $lembaga_kalibrasi = trim($_POST['lembaga_kalibrasi'] ?? '');
    $tanggal_kalibrasi = $_POST['tanggal_kalibrasi'] ?? date('Y-m-d');
    $tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'] ?? date('Y-m-d', strtotime('+1 year'));
    $hasil = $_POST['hasil'] ?? 'Laik Pakai';
    $biaya = (float)str_replace(['Rp', '.', ' ', ','], ['', '', '', '.'], $_POST['biaya'] ?? '0');
    $catatan = trim($_POST['catatan'] ?? '');

    if (empty($id_alat) || empty($nomor_sertifikat) || empty($lembaga_kalibrasi) || empty($tanggal_kalibrasi) || empty($tanggal_kadaluarsa)) {
        $error = 'Semua kolom bertanda bintang (*) wajib diisi.';
    } else {
        try {
            // Cek duplikasi nomor sertifikat
            $stmt = $pdo->prepare("SELECT id FROM kalibrasi WHERE nomor_sertifikat = ?");
            $stmt->execute([$nomor_sertifikat]);
            if ($stmt->fetch()) {
                $error = "Nomor sertifikat '{$nomor_sertifikat}' sudah pernah diinput sebelumnya.";
            } else {
                // Handle upload file dokumen / PDF
                $dokumen_nama = null;
                if (!empty($_FILES['dokumen']['name']) && $_FILES['dokumen']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['dokumen']['name'], PATHINFO_EXTENSION));
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                    if (in_array($ext, $allowed)) {
                        $dokumen_nama = 'sertifikat_' . time() . '_' . uniqid() . '.' . $ext;
                        $upload_dir = __DIR__ . '/../assets/uploads/sertifikat/';
                        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                        move_uploaded_file($_FILES['dokumen']['tmp_name'], $upload_dir . $dokumen_nama);
                    }
                }

                $stmt = $pdo->prepare("
                    INSERT INTO kalibrasi (id_alat, nomor_sertifikat, lembaga_kalibrasi, tanggal_kalibrasi, tanggal_kadaluarsa, hasil, biaya, dokumen, catatan)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $id_alat, $nomor_sertifikat, $lembaga_kalibrasi, $tanggal_kalibrasi, $tanggal_kadaluarsa, $hasil, $biaya, $dokumen_nama, $catatan
                ]);

                // Update status alat jika Tidak Laik Pakai
                if ($hasil === 'Tidak Laik Pakai') {
                    $up_alat = $pdo->prepare("UPDATE alat SET status_operasional = 'Rusak' WHERE id = ?");
                    $up_alat->execute([$id_alat]);
                } else {
                    $up_alat = $pdo->prepare("UPDATE alat SET status_operasional = 'Siap Pakai' WHERE id = ?");
                    $up_alat->execute([$id_alat]);
                }

                set_flash('success', "Sertifikat kalibrasi '{$nomor_sertifikat}' berhasil didaftarkan!");
                header("Location: " . BASE_URL . "/kalibrasi/index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan data kalibrasi: ' . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Registrasi Sertifikat Uji Kalibrasi</h4>
        <p class="text-muted small mb-0">Input hasil uji keselamatan kerja, toleransi parameter, dan masa berlaku sertifikasi.</p>
    </div>
    <a href="<?= BASE_URL; ?>/kalibrasi/index.php" class="btn btn-light border">
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
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-certificate me-2 text-info"></i>Formulir Data Sertifikat Kalibrasi</h6>
    </div>
    <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
            <?= csrf_field(); ?>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
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
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nomor Sertifikat Kalibrasi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control font-monospace fw-bold text-primary" name="nomor_sertifikat" placeholder="Contoh: SERT-BPFK-2026-XXXX" value="<?= htmlspecialchars($_POST['nomor_sertifikat'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Lembaga Penguji / Balai Kalibrasi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="lembaga_kalibrasi" list="listLembaga" placeholder="Contoh: Balai Pengujian Fasilitas Kesehatan (BPFK)" value="<?= htmlspecialchars($_POST['lembaga_kalibrasi'] ?? ''); ?>" required>
                    <datalist id="listLembaga">
                        <option value="Balai Pengujian Fasilitas Kesehatan (BPFK) Jakarta">
                        <option value="Balai Pengujian Fasilitas Kesehatan (BPFK) Surabaya">
                        <option value="Balai Pengujian Fasilitas Kesehatan (BPFK) Makassar">
                        <option value="Balai Pengujian Fasilitas Kesehatan (BPFK) Medan">
                        <option value="PT. Sucofindo (Persero)">
                        <option value="Vendor / Authorized Manufacturer Service Center">
                    </datalist>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tanggal Kalibrasi <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_kalibrasi" id="tglKalibrasi" value="<?= htmlspecialchars($_POST['tanggal_kalibrasi'] ?? date('Y-m-d')); ?>" required onchange="autoSetExpiry(this.value)">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tanggal Kadaluarsa <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_kadaluarsa" id="tglKadaluarsa" value="<?= htmlspecialchars($_POST['tanggal_kadaluarsa'] ?? date('Y-m-d', strtotime('+1 year'))); ?>" required>
                    <small class="text-muted" style="font-size: 0.72rem;">Standar berlaku 1 tahun</small>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Hasil Kelaikan Kalibrasi <span class="text-danger">*</span></label>
                    <select class="form-select fw-semibold" name="hasil" required>
                        <option value="Laik Pakai" class="text-success">Laik Pakai (Aman &amp; Lolos Standar)</option>
                        <option value="Tidak Laik Pakai" class="text-danger">Tidak Laik Pakai (Di Luar Toleransi)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Biaya Pelaksanaan Kalibrasi (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">Rp</span>
                        <input type="number" class="form-control" name="biaya" placeholder="0" value="<?= htmlspecialchars($_POST['biaya'] ?? '0'); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Unggah File Sertifikat (PDF / Gambar)</label>
                    <input type="file" class="form-control" name="dokumen" accept=".pdf,image/*">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold">Catatan Hasil Uji &amp; Parameter</label>
                <textarea class="form-control" name="catatan" rows="3" placeholder="Suhu lingkungan saat pengujian, nilai toleransi deviasi maksimum, nomor stiker kalibrasi, dll..."><?= htmlspecialchars($_POST['catatan'] ?? ''); ?></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= BASE_URL; ?>/kalibrasi/index.php" class="btn btn-light px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Simpan Data Kalibrasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function autoSetExpiry(val) {
    if (!val) return;
    const d = new Date(val);
    d.setFullYear(d.getFullYear() + 1);
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    document.getElementById('tglKadaluarsa').value = `${yyyy}-${mm}-${dd}`;
}
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
