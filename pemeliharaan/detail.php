<?php
/**
 * Detail Berita Acara Pemeliharaan (BAP)
 */
$page_title = 'Detail Berita Acara Pemeliharaan';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT p.*, a.nama_alat, a.kode_alat, a.merek, a.tipe_model, a.nomor_seri, a.lokasi, a.kategori 
    FROM pemeliharaan p 
    JOIN alat a ON p.id_alat = a.id 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$maint = $stmt->fetch();

if (!$maint) {
    set_flash('error', 'Catatan pemeliharaan tidak ditemukan.');
    header("Location: " . BASE_URL . "/pemeliharaan/index.php");
    exit;
}
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">Berita Acara Servis: <?= htmlspecialchars($maint['kode_pemeliharaan']); ?></h4>
            <?= badge_status_pemeliharaan($maint['status']); ?>
        </div>
        <p class="text-muted small mb-0">Alat: <strong><?= htmlspecialchars($maint['nama_alat']); ?></strong> (<?= htmlspecialchars($maint['kode_alat']); ?>)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL; ?>/pemeliharaan/index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
        <a href="<?= BASE_URL; ?>/pemeliharaan/cetak.php?id=<?= $id; ?>" target="_blank" class="btn btn-light border text-secondary">
            <i class="fas fa-print me-1"></i> Cetak Dokumen SPK / BAP
        </a>
        <a href="<?= BASE_URL; ?>/pemeliharaan/edit.php?id=<?= $id; ?>" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit Data
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Rincian Pengerjaan -->
    <div class="col-lg-8">
        <div class="card-custom mb-4">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="fas fa-clipboard-list text-primary me-2"></i>Laporan Hasil Tindakan Teknisi</h6>
                <span class="badge bg-light text-dark border"><?= htmlspecialchars($maint['jenis_pemeliharaan']); ?></span>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="text-muted small d-block mb-1 fw-semibold">Keluhan / Masalah Awal</label>
                    <div class="p-3 bg-light rounded-3 text-dark small">
                        <?= nl2br(htmlspecialchars($maint['keluhan'] ?: 'Tidak ada keluhan spesifik (perawatan rutin).')); ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-muted small d-block mb-1 fw-semibold">Tindakan Perbaikan &amp; Prosedur yang Dilakukan</label>
                    <div class="p-3 bg-light rounded-3 text-dark small">
                        <?= nl2br(htmlspecialchars($maint['tindakan'] ?: 'Pemeriksaan umum dan uji kelistrikan.')); ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Suku Cadang Diganti</label>
                        <span class="fw-semibold text-dark"><?= htmlspecialchars($maint['suku_cadang'] ?: 'Tidak ada suku cadang diganti'); ?></span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Total Biaya Pemeliharaan</label>
                        <span class="fw-bold text-primary fs-6"><?= format_rupiah($maint['biaya']); ?></span>
                    </div>
                </div>

                <?php if (!empty($maint['catatan'])): ?>
                    <div class="p-3 bg-warning-subtle text-dark border border-warning-subtle rounded-3 small mt-3">
                        <i class="fas fa-info-circle me-1 text-warning"></i>
                        <strong>Catatan &amp; Rekomendasi:</strong> <?= htmlspecialchars($maint['catatan']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Info Alat & Petugas -->
    <div class="col-lg-4">
        <!-- Info Alat -->
        <div class="card-custom mb-4">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="fas fa-toolbox text-secondary me-2"></i>Informasi Alat Terkait</h6>
            </div>
            <div class="card-body small">
                <div class="mb-2">
                    <span class="text-muted d-block">Kode Alat</span>
                    <a href="<?= BASE_URL; ?>/inventaris/detail.php?id=<?= $maint['id_alat']; ?>" class="font-monospace fw-bold text-primary">
                        <?= htmlspecialchars($maint['kode_alat']); ?>
                    </a>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block">Nama Alat</span>
                    <strong class="text-dark"><?= htmlspecialchars($maint['nama_alat']); ?></strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block">Merek / Seri</span>
                    <span><?= htmlspecialchars($maint['merek']); ?> (SN: <?= htmlspecialchars($maint['nomor_seri'] ?: '-'); ?>)</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block">Lokasi Ruangan</span>
                    <span><i class="fas fa-location-dot text-danger me-1"></i> <?= htmlspecialchars($maint['lokasi']); ?></span>
                </div>
            </div>
        </div>

        <!-- Info Pengerjaan -->
        <div class="card-custom">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="fas fa-user-gear text-info me-2"></i>Petugas Pelaksana</h6>
            </div>
            <div class="card-body small">
                <div class="mb-2">
                    <span class="text-muted d-block">Teknisi Bertugas</span>
                    <strong class="text-dark"><?= htmlspecialchars($maint['teknisi']); ?></strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block">Tanggal Terjadwal</span>
                    <span><?= format_tanggal($maint['tanggal_jadwal']); ?></span>
                </div>
                <div class="mb-3">
                    <span class="text-muted d-block">Tanggal Penyelesaian</span>
                    <span><?= format_tanggal($maint['tanggal_selesai']) ?: '<em class="text-muted">Belum selesai</em>'; ?></span>
                </div>
                <div class="pt-2 border-top">
                    <a href="<?= BASE_URL; ?>/pemeliharaan/cetak.php?id=<?= $id; ?>" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-print me-1"></i> Cetak Lembar BAP
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
