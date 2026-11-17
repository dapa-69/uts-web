<?php
/**
 * Modul Pemeliharaan - List & Monitoring
 */
$page_title = 'Jadwal & Log Pemeliharaan Alat';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

// Filter parameter
$filter_status = $_GET['status'] ?? '';
$filter_jenis = $_GET['jenis'] ?? '';

// Build Query
$query = "
    SELECT p.*, a.nama_alat, a.kode_alat, a.merek, a.lokasi 
    FROM pemeliharaan p 
    JOIN alat a ON p.id_alat = a.id 
    WHERE 1=1
";
$params = [];

if (!empty($filter_status)) {
    $query .= " AND p.status = ?";
    $params[] = $filter_status;
}
if (!empty($filter_jenis)) {
    $query .= " AND p.jenis_pemeliharaan = ?";
    $params[] = $filter_jenis;
}

$query .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$maint_list = $stmt->fetchAll();

// Total Biaya Pemeliharaan
$total_biaya = array_sum(array_column($maint_list, 'biaya'));
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Manajemen Pemeliharaan &amp; Servis Alat</h4>
        <p class="text-muted small mb-0">Jadwal perawatan preventif berkala, perbaikan korektif, dan suku cadang.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL; ?>/laporan/pemeliharaan.php" target="_blank" class="btn btn-light border text-secondary shadow-sm">
            <i class="fas fa-print me-1"></i> Cetak Rekap Servis
        </a>
        <a href="<?= BASE_URL; ?>/pemeliharaan/create.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Catat Pemeliharaan Baru
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card-custom mb-4">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Status Pengerjaan</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Status --</option>
                    <option value="Terjadwal" <?= ($filter_status == 'Terjadwal') ? 'selected' : ''; ?>>Terjadwal</option>
                    <option value="Sedang Dikerjakan" <?= ($filter_status == 'Sedang Dikerjakan') ? 'selected' : ''; ?>>Sedang Dikerjakan</option>
                    <option value="Selesai" <?= ($filter_status == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                    <option value="Dibatalkan" <?= ($filter_status == 'Dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Jenis Pemeliharaan</label>
                <select name="jenis" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Jenis --</option>
                    <option value="Preventif" <?= ($filter_jenis == 'Preventif') ? 'selected' : ''; ?>>Preventif (Rutin)</option>
                    <option value="Korektif" <?= ($filter_jenis == 'Korektif') ? 'selected' : ''; ?>>Korektif (Perbaikan)</option>
                    <option value="Inspeksi Rutin" <?= ($filter_jenis == 'Inspeksi Rutin') ? 'selected' : ''; ?>>Inspeksi Rutin</option>
                    <option value="Darurat" <?= ($filter_jenis == 'Darurat') ? 'selected' : ''; ?>>Darurat / Emergency</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end pt-3">
                <a href="<?= BASE_URL; ?>/pemeliharaan/index.php" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="fas fa-rotate-left me-1"></i> Reset Filter
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card-custom">
    <div class="card-header">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-screwdriver-wrench me-2 text-warning"></i>Data Pemeliharaan Alat Medis (<?= count($maint_list); ?> Tugas)</h6>
        <span class="badge bg-light text-dark border">Akumulasi Biaya: <strong><?= format_rupiah($total_biaya); ?></strong></span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-custom datatable w-100">
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th>Kode Servis</th>
                        <th>Alat &amp; Lokasi</th>
                        <th>Jenis Servis</th>
                        <th>Tanggal Jadwal</th>
                        <th>Teknisi</th>
                        <th>Tindakan / Masalah</th>
                        <th>Biaya (Rp)</th>
                        <th>Status</th>
                        <th width="12%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($maint_list as $row): 
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <a href="<?= BASE_URL; ?>/pemeliharaan/detail.php?id=<?= $row['id']; ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                    <?= htmlspecialchars($row['kode_pemeliharaan']); ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_alat']); ?></div>
                                <div class="text-muted small">
                                    <span class="font-monospace text-xs"><?= htmlspecialchars($row['kode_alat']); ?></span> &bull; 
                                    <i class="fas fa-location-dot text-danger text-xs"></i> <?= htmlspecialchars($row['lokasi']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border fw-medium"><?= htmlspecialchars($row['jenis_pemeliharaan']); ?></span>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= format_tanggal($row['tanggal_jadwal']); ?></div>
                                <?php if (!empty($row['tanggal_selesai'])): ?>
                                    <div class="text-success text-xs" style="font-size: 0.75rem;"><i class="fas fa-check"></i> Selesai: <?= date('d/m/Y', strtotime($row['tanggal_selesai'])); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="small fw-semibold text-secondary">
                                <?= htmlspecialchars($row['teknisi']); ?>
                            </td>
                            <td class="small" style="max-width: 200px;">
                                <div class="text-truncate" title="<?= htmlspecialchars($row['tindakan'] ?: $row['keluhan']); ?>">
                                    <?= htmlspecialchars($row['tindakan'] ?: $row['keluhan']); ?>
                                </div>
                                <?php if (!empty($row['suku_cadang'])): ?>
                                    <div class="text-primary text-xs text-truncate"><i class="fas fa-gear me-1"></i> <?= htmlspecialchars($row['suku_cadang']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="small fw-semibold"><?= format_rupiah($row['biaya']); ?></td>
                            <td><?= badge_status_pemeliharaan($row['status']); ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL; ?>/pemeliharaan/detail.php?id=<?= $row['id']; ?>" class="btn btn-light" data-bs-toggle="tooltip" title="Lihat Berita Acara (BAP)">
                                        <i class="far fa-file-alt text-primary"></i>
                                    </a>
                                    <a href="<?= BASE_URL; ?>/pemeliharaan/edit.php?id=<?= $row['id']; ?>" class="btn btn-light" data-bs-toggle="tooltip" title="Edit / Update Status">
                                        <i class="far fa-edit text-warning"></i>
                                    </a>
                                    <a href="<?= BASE_URL; ?>/pemeliharaan/delete.php?id=<?= $row['id']; ?>" class="btn btn-light btn-delete-confirm" data-item="Log Servis <?= htmlspecialchars($row['kode_pemeliharaan']); ?>" data-bs-toggle="tooltip" title="Hapus">
                                        <i class="far fa-trash-alt text-danger"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
