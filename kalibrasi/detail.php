<?php
/**
 * Detail Sertifikat Kalibrasi & Label Kelaikan
 */
$page_title = 'Detail Kalibrasi & Sertifikat';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT k.*, a.nama_alat, a.kode_alat, a.merek, a.tipe_model, a.nomor_seri, a.lokasi, a.kategori 
    FROM kalibrasi k 
    JOIN alat a ON k.id_alat = a.id 
    WHERE k.id = ?
");
$stmt->execute([$id]);
$cal = $stmt->fetch();

if (!$cal) {
    set_flash('error', 'Data kalibrasi tidak ditemukan.');
    header("Location: " . BASE_URL . "/kalibrasi/index.php");
    exit;
}

$exp_info = get_status_kadaluarsa_kalibrasi($cal['tanggal_kadaluarsa']);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">Sertifikat: <?= htmlspecialchars($cal['nomor_sertifikat']); ?></h4>
            <?= badge_hasil_kalibrasi($cal['hasil']); ?>
        </div>
        <p class="text-muted small mb-0">Instrumen: <strong><?= htmlspecialchars($cal['nama_alat']); ?></strong> (<?= htmlspecialchars($cal['kode_alat']); ?>)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL; ?>/kalibrasi/index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
        <a href="<?= BASE_URL; ?>/kalibrasi/cetak.php?id=<?= $id; ?>" target="_blank" class="btn btn-light border text-secondary">
            <i class="fas fa-print me-1"></i> Cetak Label Stiker &amp; Surat Uji
        </a>
        <a href="<?= BASE_URL; ?>/kalibrasi/edit.php?id=<?= $id; ?>" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit Data
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Rincian Sertifikat & Masa Berlaku -->
    <div class="col-lg-8">
        <div class="card-custom mb-4">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="fas fa-certificate text-info me-2"></i>Parameter Uji Kalibrasi</h6>
                <div><?= $exp_info['badge']; ?></div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Nomor Registrasi Sertifikat</label>
                        <span class="font-monospace fw-bold text-dark fs-6"><?= htmlspecialchars($cal['nomor_sertifikat']); ?></span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Lembaga Penguji / Vendor</label>
                        <span class="fw-semibold text-dark"><?= htmlspecialchars($cal['lembaga_kalibrasi']); ?></span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Tanggal Pelaksanaan Pengujian</label>
                        <span class="fw-semibold text-dark"><?= format_tanggal($cal['tanggal_kalibrasi']); ?></span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Tanggal Jatuh Tempo Kalibrasi Berikutnya</label>
                        <span class="fw-bold text-danger"><?= format_tanggal($cal['tanggal_kadaluarsa']); ?></span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Hasil Evaluasi Kelaikan</label>
                        <div><?= badge_hasil_kalibrasi($cal['hasil']); ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Biaya Sertifikasi Kalibrasi</label>
                        <span class="fw-bold text-primary"><?= format_rupiah($cal['biaya']); ?></span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small d-block fw-semibold mb-1">Catatan Pengujian &amp; Keselamatan Listrik</label>
                    <div class="p-3 bg-light rounded-3 text-dark small">
                        <?= nl2br(htmlspecialchars($cal['catatan'] ?: 'Seluruh parameter keluaran instrumen berada dalam rentang toleransi standar keselamatan.')); ?>
                    </div>
                </div>

                <!-- Dokumen Lampiran File -->
                <?php if (!empty($cal['dokumen']) && file_exists(__DIR__ . '/../assets/uploads/sertifikat/' . $cal['dokumen'])): ?>
                    <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between mt-3">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fas fa-file-pdf fs-2 text-danger"></i>
                            <div>
                                <div class="fw-bold small text-dark">Lampiran Dokumen Sertifikat Resmi</div>
                                <div class="text-muted text-xs"><?= htmlspecialchars($cal['dokumen']); ?></div>
                            </div>
                        </div>
                        <a href="<?= BASE_URL; ?>/assets/uploads/sertifikat/<?= htmlspecialchars($cal['dokumen']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i> Buka Dokumen
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Simulator Label Stiker Kalibrasi & Info Alat -->
    <div class="col-lg-4">
        <!-- Label Stiker Kalibrasi Simulator -->
        <div class="card-custom mb-4">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="fas fa-tag text-success me-2"></i>Simulasi Stiker Kalibrasi</h6>
                <a href="<?= BASE_URL; ?>/kalibrasi/cetak.php?id=<?= $id; ?>" target="_blank" class="btn btn-sm btn-light border"><i class="fas fa-print"></i></a>
            </div>
            <div class="card-body">
                <div class="p-3 border-2 border-<?= ($cal['hasil'] === 'Laik Pakai') ? 'success' : 'danger'; ?> rounded-3 bg-white text-center shadow-xs" style="border-style: dashed !important;">
                    <div class="badge bg-<?= ($cal['hasil'] === 'Laik Pakai') ? 'success' : 'danger'; ?> text-uppercase px-3 py-1 mb-2 fw-bold" style="font-size: 0.8rem;">
                        <i class="fas fa-check-shield me-1"></i> <?= htmlspecialchars($cal['hasil']); ?>
                    </div>
                    <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($cal['nama_alat']); ?></h6>
                    <div class="font-monospace text-xs text-primary mb-2">No. Seri: <?= htmlspecialchars($cal['nomor_seri'] ?: '-'); ?></div>
                    <div class="d-flex justify-content-between small border-top pt-2 text-start">
                        <span class="text-muted">Tgl Uji:</span>
                        <strong class="text-dark"><?= date('d/m/Y', strtotime($cal['tanggal_kalibrasi'])); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between small text-start mb-2">
                        <span class="text-muted">Kadaluarsa:</span>
                        <strong class="text-danger"><?= date('d/m/Y', strtotime($cal['tanggal_kadaluarsa'])); ?></strong>
                    </div>
                    <div class="text-muted text-xs border-top pt-2">
                        Diuji oleh: <strong><?= htmlspecialchars($cal['lembaga_kalibrasi']); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Alat -->
        <div class="card-custom">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="fas fa-microscope text-secondary me-2"></i>Alat Elektromedis</h6>
            </div>
            <div class="card-body small">
                <div class="mb-2">
                    <span class="text-muted d-block">Kode Alat</span>
                    <a href="<?= BASE_URL; ?>/inventaris/detail.php?id=<?= $cal['id_alat']; ?>" class="font-monospace fw-bold text-primary">
                        <?= htmlspecialchars($cal['kode_alat']); ?>
                    </a>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block">Ruangan / Penempatan</span>
                    <span><i class="fas fa-location-dot text-danger me-1"></i> <?= htmlspecialchars($cal['lokasi']); ?></span>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block">Merek / Pabrikan</span>
                    <span><?= htmlspecialchars($cal['merek']); ?></span>
                </div>
                <div class="pt-2 border-top">
                    <a href="<?= BASE_URL; ?>/inventaris/detail.php?id=<?= $cal['id_alat']; ?>" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-eye me-1"></i> Buka Master Alat
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
