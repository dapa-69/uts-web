<?php
/**
 * Detail Alat - Kartu Aset, Riwayat Pemeliharaan & Kalibrasi
 */
$page_title = 'Detail Alat & Riwayat Servis';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM alat WHERE id = ?");
$stmt->execute([$id]);
$alat = $stmt->fetch();

if (!$alat) {
    set_flash('error', 'Alat tidak ditemukan.');
    header("Location: " . BASE_URL . "/inventaris/index.php");
    exit;
}

// Ambil Riwayat Pemeliharaan Alat Ini
$stmt_maint = $pdo->prepare("SELECT * FROM pemeliharaan WHERE id_alat = ? ORDER BY tanggal_jadwal DESC");
$stmt_maint->execute([$id]);
$maint_history = $stmt_maint->fetchAll();

// Ambil Riwayat Kalibrasi Alat Ini
$stmt_cal = $pdo->prepare("SELECT * FROM kalibrasi WHERE id_alat = ? ORDER BY tanggal_kalibrasi DESC");
$stmt_cal->execute([$id]);
$cal_history = $stmt_cal->fetchAll();

// Kalibrasi aktif terbaru jika ada
$latest_cal = !empty($cal_history) ? $cal_history[0] : null;
$cal_status = $latest_cal ? get_status_kadaluarsa_kalibrasi($latest_cal['tanggal_kadaluarsa']) : null;
?>

<!-- Action Bar -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0"><?= htmlspecialchars($alat['nama_alat']); ?></h4>
            <span class="badge bg-primary font-monospace"><?= htmlspecialchars($alat['kode_alat']); ?></span>
        </div>
        <p class="text-muted small mb-0">Lokasi: <strong><?= htmlspecialchars($alat['lokasi']); ?></strong> &bull; Kategori: <strong><?= htmlspecialchars($alat['kategori']); ?></strong></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL; ?>/inventaris/index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
        <a href="<?= BASE_URL; ?>/pemeliharaan/create.php?id_alat=<?= $id; ?>" class="btn btn-warning text-dark fw-bold">
            <i class="fas fa-screwdriver-wrench me-1"></i> Catat Servis
        </a>
        <a href="<?= BASE_URL; ?>/kalibrasi/create.php?id_alat=<?= $id; ?>" class="btn btn-info text-white fw-bold">
            <i class="fas fa-certificate me-1"></i> Input Kalibrasi
        </a>
        <a href="<?= BASE_URL; ?>/inventaris/edit.php?id=<?= $id; ?>" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit Data
        </a>
    </div>
</div>

<!-- Profil Alat & Asset Tag Card -->
<div class="row g-4 mb-4">
    <!-- Kolom Kiri: Spesifikasi Aset -->
    <div class="col-lg-8">
        <div class="card-custom h-100">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="fas fa-circle-info text-primary me-2"></i>Spesifikasi Master Instrumen Medis</h6>
                <?= badge_status_operasional($alat['status_operasional']); ?>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Nama Alat</label>
                        <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($alat['nama_alat']); ?></span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Merek &amp; Tipe Model</label>
                        <span class="fw-semibold text-dark"><?= htmlspecialchars($alat['merek']); ?> <?= !empty($alat['tipe_model']) ? ' / ' . htmlspecialchars($alat['tipe_model']) : ''; ?></span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Nomor Seri Pabrik (Serial Number)</label>
                        <span class="font-monospace text-primary fw-bold"><?= htmlspecialchars($alat['nomor_seri'] ?: '-'); ?></span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Kondisi Fisik</label>
                        <span><?= badge_kondisi($alat['kondisi']); ?></span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Lokasi Penempatan / Ruangan</label>
                        <span class="fw-semibold text-dark"><i class="fas fa-location-dot text-danger me-1"></i> <?= htmlspecialchars($alat['lokasi']); ?></span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Tahun Pengadaan &amp; Nilai</label>
                        <span class="fw-semibold text-dark">Tahun <?= $alat['tahun']; ?> (<?= format_rupiah($alat['harga_pengadaan']); ?>)</span>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small d-block">Spesifikasi Teknis &amp; Catatan Khusus</label>
                        <div class="p-3 bg-light rounded-3 text-secondary small">
                            <?= nl2br(htmlspecialchars($alat['spesifikasi'] ?: 'Tidak ada catatan spesifikasi tambahan.')); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Foto & Kartu Label Aset (Asset Tag Mockup) -->
    <div class="col-lg-4">
        <div class="card-custom h-100">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="fas fa-barcode text-secondary me-2"></i>Label Identitas Aset</h6>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fas fa-print me-1"></i> Cetak Tag</button>
            </div>
            <div class="card-body text-center">
                <?php if (!empty($alat['foto']) && file_exists(__DIR__ . '/../assets/uploads/alat/' . $alat['foto'])): ?>
                    <img src="<?= BASE_URL; ?>/assets/uploads/alat/<?= htmlspecialchars($alat['foto']); ?>" alt="Foto Alat" class="img-fluid rounded-3 mb-3 shadow-sm" style="max-height: 180px; object-fit: cover; width: 100%;">
                <?php else: ?>
                    <div class="bg-light rounded-3 d-flex flex-column align-items-center justify-content-center p-4 mb-3 border">
                        <i class="fas fa-microscope text-muted fs-1 mb-2"></i>
                        <span class="text-muted small">Tidak ada foto alat diunggah</span>
                    </div>
                <?php endif; ?>

                <!-- Label Sticker Simulator -->
                <div class="p-3 border rounded-3 bg-white text-start shadow-xs">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <span class="fw-bold text-dark small">RUMAH SAKIT / KLINIK</span>
                        <span class="badge bg-primary font-monospace"><?= htmlspecialchars($alat['kode_alat']); ?></span>
                    </div>
                    <div class="small fw-bold text-dark text-truncate"><?= htmlspecialchars($alat['nama_alat']); ?></div>
                    <div class="text-muted text-xs" style="font-size: 0.75rem;">SN: <?= htmlspecialchars($alat['nomor_seri'] ?: '-'); ?></div>
                    <div class="text-muted text-xs mb-2" style="font-size: 0.75rem;">Ruang: <?= htmlspecialchars($alat['lokasi']); ?></div>
                    <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                        <div style="font-size: 0.72rem;">
                            Status Kalibrasi:
                            <div><?= $cal_status ? $cal_status['badge'] : '<span class="badge bg-secondary">Belum ada uji</span>'; ?></div>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-qrcode fs-2 text-dark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sub-Tabs: Riwayat Pemeliharaan & Riwayat Kalibrasi -->
<div class="card-custom">
    <div class="card-header p-0">
        <ul class="nav nav-tabs border-0" id="detailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active px-4 py-3 fw-semibold" id="maint-tab" data-bs-toggle="tab" data-bs-target="#maint-tab-pane" type="button" role="tab">
                    <i class="fas fa-screwdriver-wrench me-2 text-warning"></i>Riwayat Pemeliharaan (<?= count($maint_history); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4 py-3 fw-semibold" id="cal-tab" data-bs-toggle="tab" data-bs-target="#cal-tab-pane" type="button" role="tab">
                    <i class="fas fa-certificate me-2 text-info"></i>Riwayat Kalibrasi &amp; Uji Laik (<?= count($cal_history); ?>)
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="detailTabsContent">
            <!-- Tab 1: Pemeliharaan -->
            <div class="tab-pane fade show active" id="maint-tab-pane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark">Log Servis &amp; Perawatan Berkala</h6>
                    <a href="<?= BASE_URL; ?>/pemeliharaan/create.php?id_alat=<?= $id; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Catat Pemeliharaan Baru
                    </a>
                </div>

                <?php if (empty($maint_history)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-clipboard-check fs-2 text-muted mb-2 d-block"></i>
                        Belum ada catatan pemeliharaan untuk alat ini.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Kode Servis</th>
                                    <th>Jenis</th>
                                    <th>Tgl Jadwal / Selesai</th>
                                    <th>Teknisi</th>
                                    <th>Tindakan / Suku Cadang</th>
                                    <th>Biaya</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($maint_history as $m): ?>
                                    <tr>
                                        <td class="font-monospace fw-bold text-dark"><?= htmlspecialchars($m['kode_pemeliharaan']); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($m['jenis_pemeliharaan']); ?></span></td>
                                        <td class="small">
                                            <div>Jadwal: <?= format_tanggal($m['tanggal_jadwal']); ?></div>
                                            <div class="text-muted">Selesai: <?= format_tanggal($m['tanggal_selesai']); ?></div>
                                        </td>
                                        <td class="small fw-semibold"><?= htmlspecialchars($m['teknisi']); ?></td>
                                        <td class="small" style="max-width: 250px;">
                                            <div><?= htmlspecialchars($m['tindakan'] ?: $m['keluhan']); ?></div>
                                            <?php if (!empty($m['suku_cadang'])): ?>
                                                <div class="text-primary text-xs"><i class="fas fa-gear me-1"></i> <?= htmlspecialchars($m['suku_cadang']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small"><?= format_rupiah($m['biaya']); ?></td>
                                        <td><?= badge_status_pemeliharaan($m['status']); ?></td>
                                        <td class="text-center">
                                            <a href="<?= BASE_URL; ?>/pemeliharaan/edit.php?id=<?= $m['id']; ?>" class="btn btn-sm btn-light" data-bs-toggle="tooltip" title="Edit Log">
                                                <i class="fas fa-edit text-warning"></i>
                                            </a>
                                            <a href="<?= BASE_URL; ?>/pemeliharaan/detail.php?id=<?= $m['id']; ?>" class="btn btn-sm btn-light" data-bs-toggle="tooltip" title="Lihat SPK/BAP">
                                                <i class="fas fa-file-lines text-primary"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab 2: Kalibrasi -->
            <div class="tab-pane fade" id="cal-tab-pane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark">Sertifikat Kalibrasi &amp; Uji Keselamatan Listrik</h6>
                    <a href="<?= BASE_URL; ?>/kalibrasi/create.php?id_alat=<?= $id; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Catat Hasil Kalibrasi Baru
                    </a>
                </div>

                <?php if (empty($cal_history)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-award fs-2 text-muted mb-2 d-block"></i>
                        Belum ada data riwayat kalibrasi untuk alat ini.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>No. Sertifikat</th>
                                    <th>Lembaga Penguji</th>
                                    <th>Tgl Kalibrasi</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Hasil Uji</th>
                                    <th>Status Masa Berlaku</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cal_history as $c): 
                                    $exp_info = get_status_kadaluarsa_kalibrasi($c['tanggal_kadaluarsa']);
                                ?>
                                    <tr>
                                        <td class="font-monospace fw-bold text-dark"><?= htmlspecialchars($c['nomor_sertifikat']); ?></td>
                                        <td class="small fw-semibold text-secondary"><?= htmlspecialchars($c['lembaga_kalibrasi']); ?></td>
                                        <td class="small"><?= format_tanggal($c['tanggal_kalibrasi']); ?></td>
                                        <td class="small fw-semibold"><?= format_tanggal($c['tanggal_kadaluarsa']); ?></td>
                                        <td><?= badge_hasil_kalibrasi($c['hasil']); ?></td>
                                        <td><?= $exp_info['badge']; ?></td>
                                        <td class="text-center">
                                            <a href="<?= BASE_URL; ?>/kalibrasi/edit.php?id=<?= $c['id']; ?>" class="btn btn-sm btn-light" data-bs-toggle="tooltip" title="Edit Sertifikat">
                                                <i class="fas fa-edit text-warning"></i>
                                            </a>
                                            <a href="<?= BASE_URL; ?>/kalibrasi/detail.php?id=<?= $c['id']; ?>" class="btn btn-sm btn-light" data-bs-toggle="tooltip" title="Lihat Sertifikat">
                                                <i class="fas fa-eye text-primary"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
