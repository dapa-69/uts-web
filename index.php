<?php
/**
 * Main Dashboard - SIMRS Pro
 */
$page_title = 'Executive Dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/includes/navbar.php';

// Ambil Statistik Agregat dari Database
try {
    // 1. Total Alat
    $total_alat = $pdo->query("SELECT COUNT(*) FROM alat")->fetchColumn();
    
    // 2. Alat Siap Pakai
    $total_siap_pakai = $pdo->query("SELECT COUNT(*) FROM alat WHERE status_operasional = 'Siap Pakai'")->fetchColumn();
    $percent_siap = $total_alat > 0 ? round(($total_siap_pakai / $total_alat) * 100) : 0;
    
    // 3. Pemeliharaan Berjalan
    $total_maint_active = $pdo->query("SELECT COUNT(*) FROM pemeliharaan WHERE status IN ('Terjadwal', 'Sedang Dikerjakan')")->fetchColumn();
    
    // 4. Kalibrasi Perlu Perhatian (Kadaluarsa atau <= 30 hari)
    $total_kalibrasi_urgent = $pdo->query("SELECT COUNT(*) FROM kalibrasi WHERE tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
    
    // 5. Total Nilai Investasi Aset
    $total_nilai_aset = $pdo->query("SELECT SUM(harga_pengadaan) FROM alat")->fetchColumn() ?: 0;

    // Data Chart: Kondisi Alat
    $kondisi_stats = $pdo->query("
        SELECT kondisi, COUNT(*) as count 
        FROM alat 
        GROUP BY kondisi
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    $kondisi_baik = $kondisi_stats['Baik'] ?? 0;
    $kondisi_rusak_ringan = $kondisi_stats['Rusak Ringan'] ?? 0;
    $kondisi_rusak_berat = $kondisi_stats['Rusak Berat'] ?? 0;

    // Data Chart: Jenis Pemeliharaan
    $maint_types = $pdo->query("
        SELECT jenis_pemeliharaan, COUNT(*) as count 
        FROM pemeliharaan 
        GROUP BY jenis_pemeliharaan
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    // List Kalibrasi Jatuh Tempo & Kadaluarsa (Urgent List)
    $stmt_urgent_cal = $pdo->query("
        SELECT k.*, a.nama_alat, a.kode_alat, a.lokasi 
        FROM kalibrasi k 
        JOIN alat a ON k.id_alat = a.id 
        WHERE k.tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
        ORDER BY k.tanggal_kadaluarsa ASC 
        LIMIT 5
    ");
    $urgent_calibrations = $stmt_urgent_cal->fetchAll();

    // List Pemeliharaan Terkini
    $stmt_recent_maint = $pdo->query("
        SELECT p.*, a.nama_alat, a.kode_alat, a.lokasi 
        FROM pemeliharaan p 
        JOIN alat a ON p.id_alat = a.id 
        ORDER BY p.id DESC 
        LIMIT 5
    ");
    $recent_maintenances = $stmt_recent_maint->fetchAll();

} catch (Exception $e) {
    $total_alat = 0;
    $total_siap_pakai = 0;
    $percent_siap = 0;
    $total_maint_active = 0;
    $total_kalibrasi_urgent = 0;
    $total_nilai_aset = 0;
    $kondisi_baik = 0;
    $kondisi_rusak_ringan = 0;
    $kondisi_rusak_berat = 0;
    $maint_types = [];
    $urgent_calibrations = [];
    $recent_maintenances = [];
}
?>

<!-- Header Dashboard & Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 font-monospace" style="font-size: 0.75rem;">
                        <i class="fas fa-heart-pulse heartbeat me-1"></i> CARDIOCARE SIMRS ELEKTROMEDIS
                    </span>
                    <h2 class="fw-bold mb-1 text-white">Selamat Datang, <?= htmlspecialchars($user['nama_lengkap']); ?>!</h2>
                    <p class="text-white-50 mb-0 small">
                        Monitoring terpadu inventaris medis, log pemeliharaan preventif, dan kelaikan uji kalibrasi berkala.
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= BASE_URL; ?>/inventaris/create.php" class="btn btn-light fw-bold" style="color: var(--primary);">
                        <i class="fas fa-plus me-1"></i> Tambah Alat
                    </a>
                    <a href="<?= BASE_URL; ?>/pemeliharaan/create.php" class="btn btn-outline-light">
                        <i class="fas fa-wrench me-1"></i> Catat Servis
                    </a>
                    <a href="<?= BASE_URL; ?>/kalibrasi/create.php" class="btn btn-outline-light">
                        <i class="fas fa-certificate me-1"></i> Uji Kalibrasi
                    </a>
                    <a href="<?= BASE_URL; ?>/index.html" target="_blank" class="btn btn-outline-light d-none d-lg-inline-flex align-items-center gap-1">
                        <i class="fas fa-arrow-up-right-from-square"></i> Web Portal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4 Key Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Card 1: Total Alat -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted fw-semibold small text-uppercase">Total Inventaris</span>
                <div class="stat-icon red">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-dark mb-0"><?= number_format($total_alat); ?></h3>
                <span class="text-muted small">Unit Alat</span>
            </div>
            <div class="mt-2 text-muted" style="font-size: 0.78rem;">
                <i class="fas fa-coins text-warning me-1"></i> Nilai Aset: <strong><?= format_rupiah($total_nilai_aset); ?></strong>
            </div>
        </div>
    </div>

    <!-- Card 2: Alat Siap Pakai -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted fw-semibold small text-uppercase">Siap Beroperasi</span>
                <div class="stat-icon green">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-success mb-0"><?= number_format($total_siap_pakai); ?></h3>
                <span class="badge bg-success-subtle text-success rounded-pill fw-bold"><?= $percent_siap; ?>%</span>
            </div>
            <div class="mt-2 text-muted" style="font-size: 0.78rem;">
                <i class="fas fa-shield-halved text-success me-1"></i> Kondisi optimal &amp; aman pasien
            </div>
        </div>
    </div>

    <!-- Card 3: Pemeliharaan Aktif -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted fw-semibold small text-uppercase">Pemeliharaan Aktif</span>
                <div class="stat-icon amber">
                    <i class="fas fa-screwdriver-wrench"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-warning mb-0"><?= number_format($total_maint_active); ?></h3>
                <span class="text-muted small">Tugas</span>
            </div>
            <div class="mt-2 text-muted" style="font-size: 0.78rem;">
                <i class="fas fa-clock text-warning me-1"></i> Sedang berjalan &amp; terjadwal
            </div>
        </div>
    </div>

    <!-- Card 4: Kalibrasi Urgent -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted fw-semibold small text-uppercase">Jatuh Tempo Kalibrasi</span>
                <div class="stat-icon rose">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h3 class="fw-bold text-danger mb-0"><?= number_format($total_kalibrasi_urgent); ?></h3>
                <span class="text-muted small">Perlu Tindakan</span>
            </div>
            <div class="mt-2 text-muted" style="font-size: 0.78rem;">
                <i class="fas fa-calendar-xmark text-danger me-1"></i> Expired atau &le; 30 hari lagi
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <!-- Chart 1: Distribusi Kondisi Alat -->
    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-pie text-primary me-2"></i>Distribusi Kondisi Fisik Alat</h6>
                    <small class="text-muted">Status kelayakan fisik instrumen medis</small>
                </div>
                <span class="badge bg-light text-muted border">Real-time</span>
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <div style="width: 100%; max-width: 320px; height: 260px;">
                    <canvas id="kondisiChart"></canvas>
                </div>
                <div class="d-flex justify-content-center gap-4 mt-3 w-100 pt-3 border-top">
                    <div class="text-center">
                        <span class="d-block text-muted small"><i class="fas fa-circle text-success me-1"></i> Baik</span>
                        <strong class="text-dark fs-6"><?= $kondisi_baik; ?></strong>
                    </div>
                    <div class="text-center">
                        <span class="d-block text-muted small"><i class="fas fa-circle text-warning me-1"></i> Rusak Ringan</span>
                        <strong class="text-dark fs-6"><?= $kondisi_rusak_ringan; ?></strong>
                    </div>
                    <div class="text-center">
                        <span class="d-block text-muted small"><i class="fas fa-circle text-danger me-1"></i> Rusak Berat</span>
                        <strong class="text-dark fs-6"><?= $kondisi_rusak_berat; ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 2: Komposisi Jenis Pemeliharaan -->
    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-bar text-info me-2"></i>Aktivitas Pemeliharaan Fasilitas</h6>
                    <small class="text-muted">Rasio Preventif, Korektif, dan Inspeksi</small>
                </div>
                <a href="<?= BASE_URL; ?>/pemeliharaan/index.php" class="btn btn-sm btn-light">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div style="width: 100%; height: 280px;">
                    <canvas id="pemeliharaanChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert & Recent Activities Tables -->
<div class="row g-4">
    <!-- Peringatan Kalibrasi Mendesak -->
    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger rounded-circle p-2"><i class="fas fa-bell text-white"></i></span>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Peringatan Kalibrasi Mendesak</h6>
                        <small class="text-muted">Alat yang wajib segera diuji ulang</small>
                    </div>
                </div>
                <a href="<?= BASE_URL; ?>/kalibrasi/index.php" class="btn btn-sm btn-outline-danger">Kelola Kalibrasi</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($urgent_calibrations)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-circle-check text-success fs-1 mb-2 d-block"></i>
                        <p class="mb-0">Semua alat memiliki masa kalibrasi yang aman dan berlaku.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Kode &amp; Nama Alat</th>
                                    <th>Lokasi</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($urgent_calibrations as $c): 
                                    $exp_status = get_status_kadaluarsa_kalibrasi($c['tanggal_kadaluarsa']);
                                ?>
                                    <tr>
                                        <td>
                                            <a href="<?= BASE_URL; ?>/inventaris/detail.php?id=<?= $c['id_alat']; ?>" class="fw-bold text-decoration-none text-primary">
                                                <?= htmlspecialchars($c['kode_alat']); ?>
                                            </a>
                                            <div class="small text-dark fw-medium text-truncate" style="max-width: 180px;">
                                                <?= htmlspecialchars($c['nama_alat']); ?>
                                            </div>
                                        </td>
                                        <td class="small text-muted"><?= htmlspecialchars($c['lokasi']); ?></td>
                                        <td class="small fw-semibold"><?= date('d/m/Y', strtotime($c['tanggal_kadaluarsa'])); ?></td>
                                        <td><?= $exp_status['badge']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Riwayat & Agenda Pemeliharaan Terkini -->
    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list-check text-warning me-2"></i>Pemeliharaan Terkini</h6>
                    <small class="text-muted">Jadwal servis dan perbaikan alat elektromedis</small>
                </div>
                <a href="<?= BASE_URL; ?>/pemeliharaan/index.php" class="btn btn-sm btn-light">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_maintenances)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-tools text-muted fs-1 mb-2 d-block"></i>
                        <p class="mb-0">Belum ada riwayat pemeliharaan tercatat.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Kode / Alat</th>
                                    <th>Jenis</th>
                                    <th>Teknisi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_maintenances as $m): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark small"><?= htmlspecialchars($m['kode_pemeliharaan']); ?></div>
                                            <div class="text-muted small text-truncate" style="max-width: 160px;">
                                                <?= htmlspecialchars($m['nama_alat']); ?>
                                            </div>
                                        </td>
                                        <td class="small">
                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($m['jenis_pemeliharaan']); ?></span>
                                        </td>
                                        <td class="small text-muted"><?= htmlspecialchars($m['teknisi']); ?></td>
                                        <td><?= badge_status_pemeliharaan($m['status']); ?></td>
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
require_once __DIR__ . '/includes/footer.php';
?>

<!-- Chart.js Configuration -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDarkMode = () => document.documentElement.getAttribute('data-theme') === 'dark';
    const getTextColor = () => isDarkMode() ? '#94a3b8' : '#64748b';
    const getGridColor = () => isDarkMode() ? '#1f293d' : '#f1f5f9';

    // 1. Chart Kondisi Alat (Doughnut)
    const ctxKondisi = document.getElementById('kondisiChart');
    let chartKondisi = null;
    if (ctxKondisi) {
        chartKondisi = new Chart(ctxKondisi, {
            type: 'doughnut',
            data: {
                labels: ['Baik', 'Rusak Ringan', 'Rusak Berat'],
                datasets: [{
                    data: [<?= (int)$kondisi_baik; ?>, <?= (int)$kondisi_rusak_ringan; ?>, <?= (int)$kondisi_rusak_berat; ?>],
                    backgroundColor: ['#10b981', '#f59e0b', '#dc2626'],
                    borderWidth: 2,
                    borderColor: isDarkMode() ? '#131b2e' : '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { 
                            boxWidth: 12, 
                            font: { family: 'Plus Jakarta Sans', size: 12 },
                            color: getTextColor()
                        }
                    }
                },
                cutout: '72%'
            }
        });
    }

    // 2. Chart Jenis Pemeliharaan (Bar)
    const ctxMaint = document.getElementById('pemeliharaanChart');
    let chartMaint = null;
    if (ctxMaint) {
        const labelsMaint = <?= json_encode(array_keys($maint_types)); ?>;
        const dataMaint = <?= json_encode(array_values($maint_types)); ?>;

        chartMaint = new Chart(ctxMaint, {
            type: 'bar',
            data: {
                labels: labelsMaint.length ? labelsMaint : ['Preventif', 'Korektif', 'Inspeksi Rutin'],
                datasets: [{
                    label: 'Jumlah Tugas',
                    data: dataMaint.length ? dataMaint : [2, 1, 1],
                    backgroundColor: ['#dc2626', '#f59e0b', '#10b981', '#0ea5e9'],
                    borderRadius: 8,
                    maxBarThickness: 36
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { family: 'Plus Jakarta Sans' }, color: getTextColor() },
                        grid: { color: getGridColor() }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Plus Jakarta Sans' }, color: getTextColor() }
                    }
                }
            }
        });
    }

    // Update charts on theme change
    window.addEventListener('themeChanged', function() {
        const dark = isDarkMode();
        const textCol = getTextColor();
        const gridCol = getGridColor();

        if (chartKondisi) {
            chartKondisi.data.datasets[0].borderColor = dark ? '#131b2e' : '#ffffff';
            chartKondisi.options.plugins.legend.labels.color = textCol;
            chartKondisi.update();
        }

        if (chartMaint) {
            chartMaint.options.scales.y.ticks.color = textCol;
            chartMaint.options.scales.y.grid.color = gridCol;
            chartMaint.options.scales.x.ticks.color = textCol;
            chartMaint.update();
        }
    });
});
</script>
