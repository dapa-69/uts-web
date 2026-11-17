<?php
/**
 * Modul Kalibrasi - List & Pengawasan Masa Berlaku
 */
$page_title = 'Pengawasan Kalibrasi Alat Medis';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

// Filter parameter
$filter_hasil = $_GET['hasil'] ?? '';
$filter_status = $_GET['status_kadaluarsa'] ?? '';

// Build Query
$query = "
    SELECT k.*, a.nama_alat, a.kode_alat, a.merek, a.lokasi 
    FROM kalibrasi k 
    JOIN alat a ON k.id_alat = a.id 
    WHERE 1=1
";
$params = [];

if (!empty($filter_hasil)) {
    $query .= " AND k.hasil = ?";
    $params[] = $filter_hasil;
}

if ($filter_status === 'kadaluarsa') {
    $query .= " AND k.tanggal_kadaluarsa < CURDATE()";
} elseif ($filter_status === 'mendekati') {
    $query .= " AND k.tanggal_kadaluarsa BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
} elseif ($filter_status === 'berlaku') {
    $query .= " AND k.tanggal_kadaluarsa > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
}

$query .= " ORDER BY k.tanggal_kadaluarsa ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$kalibrasi_list = $stmt->fetchAll();

// Hitung rekap kalibrasi
$total_uji = count($kalibrasi_list);
$total_biaya_kal = array_sum(array_column($kalibrasi_list, 'biaya'));
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Manajemen Kalibrasi &amp; Uji Laik Pakai</h4>
        <p class="text-muted small mb-0">Sertifikasi keselamatan listrik, uji kinerja, dan pengawasan masa berlaku BPFK / Kemenkes.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL; ?>/laporan/kalibrasi.php" target="_blank" class="btn btn-light border text-secondary shadow-sm">
            <i class="fas fa-print me-1"></i> Cetak Rekap Kalibrasi
        </a>
        <a href="<?= BASE_URL; ?>/kalibrasi/create.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Input Kalibrasi Baru
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card-custom mb-4">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Status Masa Berlaku Sertifikat</label>
                <select name="status_kadaluarsa" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Masa Berlaku --</option>
                    <option value="kadaluarsa" <?= ($filter_status == 'kadaluarsa') ? 'selected' : ''; ?>>Sudah Kadaluarsa (Expired)</option>
                    <option value="mendekati" <?= ($filter_status == 'mendekati') ? 'selected' : ''; ?>>Jatuh Tempo dlm 30 Hari</option>
                    <option value="berlaku" <?= ($filter_status == 'berlaku') ? 'selected' : ''; ?>>Masih Berlaku Aman</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Hasil Uji / Kelaikan</label>
                <select name="hasil" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Hasil Uji --</option>
                    <option value="Laik Pakai" <?= ($filter_hasil == 'Laik Pakai') ? 'selected' : ''; ?>>Laik Pakai</option>
                    <option value="Tidak Laik Pakai" <?= ($filter_hasil == 'Tidak Laik Pakai') ? 'selected' : ''; ?>>Tidak Laik Pakai</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end pt-3">
                <a href="<?= BASE_URL; ?>/kalibrasi/index.php" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="fas fa-rotate-left me-1"></i> Reset Filter
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card-custom">
    <div class="card-header">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-award me-2 text-info"></i>Daftar Sertifikat Kalibrasi (<?= $total_uji; ?> Data)</h6>
        <span class="badge bg-light text-dark border">Total Investasi Kalibrasi: <strong><?= format_rupiah($total_biaya_kal); ?></strong></span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-custom datatable w-100">
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th>No. Sertifikat</th>
                        <th>Alat Medis</th>
                        <th>Lembaga Penguji / Vendor</th>
                        <th>Tgl Kalibrasi</th>
                        <th>Tgl Kadaluarsa</th>
                        <th>Status Masa Berlaku</th>
                        <th>Hasil Uji</th>
                        <th>Biaya (Rp)</th>
                        <th width="12%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($kalibrasi_list as $row): 
                        $exp_info = get_status_kadaluarsa_kalibrasi($row['tanggal_kadaluarsa']);
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <a href="<?= BASE_URL; ?>/kalibrasi/detail.php?id=<?= $row['id']; ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                    <?= htmlspecialchars($row['nomor_sertifikat']); ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_alat']); ?></div>
                                <div class="text-muted small">
                                    <span class="font-monospace text-xs"><?= htmlspecialchars($row['kode_alat']); ?></span> &bull; 
                                    <i class="fas fa-location-dot text-danger text-xs"></i> <?= htmlspecialchars($row['lokasi']); ?>
                                </div>
                            </td>
                            <td class="small fw-semibold text-secondary">
                                <?= htmlspecialchars($row['lembaga_kalibrasi']); ?>
                            </td>
                            <td class="small"><?= format_tanggal($row['tanggal_kalibrasi']); ?></td>
                            <td class="small fw-bold"><?= format_tanggal($row['tanggal_kadaluarsa']); ?></td>
                            <td><?= $exp_info['badge']; ?></td>
                            <td><?= badge_hasil_kalibrasi($row['hasil']); ?></td>
                            <td class="small fw-semibold"><?= format_rupiah($row['biaya']); ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL; ?>/kalibrasi/detail.php?id=<?= $row['id']; ?>" class="btn btn-light" data-bs-toggle="tooltip" title="Lihat Detail Sertifikat">
                                        <i class="far fa-file-alt text-primary"></i>
                                    </a>
                                    <a href="<?= BASE_URL; ?>/kalibrasi/edit.php?id=<?= $row['id']; ?>" class="btn btn-light" data-bs-toggle="tooltip" title="Edit Data">
                                        <i class="far fa-edit text-warning"></i>
                                    </a>
                                    <a href="<?= BASE_URL; ?>/kalibrasi/delete.php?id=<?= $row['id']; ?>" class="btn btn-light btn-delete-confirm" data-item="Sertifikat <?= htmlspecialchars($row['nomor_sertifikat']); ?>" data-bs-toggle="tooltip" title="Hapus">
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
