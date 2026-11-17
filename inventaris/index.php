<?php
/**
 * Modul Inventaris Alat - List & Filter
 */
$page_title = 'Daftar Inventaris Alat Medis';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

// Filter parameter
$filter_kondisi = $_GET['kondisi'] ?? '';
$filter_lokasi = $_GET['lokasi'] ?? '';
$filter_kategori = $_GET['kategori'] ?? '';

// Build Query
$query = "SELECT * FROM alat WHERE 1=1";
$params = [];

if (!empty($filter_kondisi)) {
    $query .= " AND kondisi = ?";
    $params[] = $filter_kondisi;
}
if (!empty($filter_lokasi)) {
    $query .= " AND lokasi = ?";
    $params[] = $filter_lokasi;
}
if (!empty($filter_kategori)) {
    $query .= " AND kategori = ?";
    $params[] = $filter_kategori;
}

$query .= " ORDER BY id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$alat_list = $stmt->fetchAll();

// Ambil daftar lokasi unik untuk opsi filter dropdown
$daftar_lokasi = $pdo->query("SELECT DISTINCT lokasi FROM alat WHERE lokasi IS NOT NULL AND lokasi != '' ORDER BY lokasi ASC")->fetchAll(PDO::FETCH_COLUMN);
$daftar_kategori = $pdo->query("SELECT DISTINCT kategori FROM alat WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Inventaris Fasilitas &amp; Alat Elektromedis</h4>
        <p class="text-muted small mb-0">Manajemen aset medis, riwayat spesifikasi, dan status operasional.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL; ?>/inventaris/export.php" class="btn btn-light border text-secondary shadow-sm">
            <i class="fas fa-file-excel text-success me-1"></i> Ekspor CSV
        </a>
        <a href="<?= BASE_URL; ?>/laporan/inventaris.php" target="_blank" class="btn btn-light border text-secondary shadow-sm">
            <i class="fas fa-print me-1"></i> Cetak Laporan
        </a>
        <a href="<?= BASE_URL; ?>/inventaris/create.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Tambah Alat Baru
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card-custom mb-4">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Filter Kategori</label>
                <select name="kategori" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Kategori --</option>
                    <?php foreach ($daftar_kategori as $kat): ?>
                        <option value="<?= htmlspecialchars($kat); ?>" <?= ($filter_kategori == $kat) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Filter Lokasi Ruangan</label>
                <select name="lokasi" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Lokasi Ruangan --</option>
                    <?php foreach ($daftar_lokasi as $lok): ?>
                        <option value="<?= htmlspecialchars($lok); ?>" <?= ($filter_lokasi == $lok) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($lok); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Filter Kondisi</label>
                <select name="kondisi" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Kondisi --</option>
                    <option value="Baik" <?= ($filter_kondisi == 'Baik') ? 'selected' : ''; ?>>Baik</option>
                    <option value="Rusak Ringan" <?= ($filter_kondisi == 'Rusak Ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                    <option value="Rusak Berat" <?= ($filter_kondisi == 'Rusak Berat') ? 'selected' : ''; ?>>Rusak Berat</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end pt-3">
                <a href="<?= BASE_URL; ?>/inventaris/index.php" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="fas fa-rotate-left me-1"></i> Reset Filter
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card-custom">
    <div class="card-header">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2 text-primary"></i>Daftar Master Aset (<?= count($alat_list); ?> Unit)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-custom datatable w-100">
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th>Kode Alat</th>
                        <th>Nama &amp; Merek</th>
                        <th>Kategori</th>
                        <th>No. Seri</th>
                        <th>Lokasi</th>
                        <th>Pengadaan</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th width="12%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($alat_list as $row): 
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <a href="<?= BASE_URL; ?>/inventaris/detail.php?id=<?= $row['id']; ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= htmlspecialchars($row['kode_alat']); ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_alat']); ?></div>
                                <div class="text-muted small">
                                    <?= htmlspecialchars($row['merek']); ?> <?= !empty($row['tipe_model']) ? ' - ' . htmlspecialchars($row['tipe_model']) : ''; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border"><?= htmlspecialchars($row['kategori']); ?></span>
                            </td>
                            <td class="font-monospace small text-muted">
                                <?= !empty($row['nomor_seri']) ? htmlspecialchars($row['nomor_seri']) : '-'; ?>
                            </td>
                            <td>
                                <i class="fas fa-location-dot text-danger small me-1"></i>
                                <?= htmlspecialchars($row['lokasi']); ?>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= $row['tahun']; ?></div>
                                <div class="text-muted text-xs" style="font-size: 0.75rem;"><?= format_rupiah($row['harga_pengadaan']); ?></div>
                            </td>
                            <td><?= badge_kondisi($row['kondisi']); ?></td>
                            <td><?= badge_status_operasional($row['status_operasional']); ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL; ?>/inventaris/detail.php?id=<?= $row['id']; ?>" class="btn btn-light" data-bs-toggle="tooltip" title="Lihat Detail & Riwayat">
                                        <i class="far fa-eye text-primary"></i>
                                    </a>
                                    <a href="<?= BASE_URL; ?>/inventaris/edit.php?id=<?= $row['id']; ?>" class="btn btn-light" data-bs-toggle="tooltip" title="Edit Data">
                                        <i class="far fa-edit text-warning"></i>
                                    </a>
                                    <a href="<?= BASE_URL; ?>/inventaris/delete.php?id=<?= $row['id']; ?>" class="btn btn-light btn-delete-confirm" data-item="Alat <?= htmlspecialchars($row['nama_alat']); ?>" data-bs-toggle="tooltip" title="Hapus">
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
