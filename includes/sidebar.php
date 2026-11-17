<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Ambil jumlah alert kalibrasi & pemeliharaan untuk badge sidebar
try {
    global $pdo;
    $count_kalibrasi_alert = $pdo->query("SELECT COUNT(*) FROM kalibrasi WHERE tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
    $count_maint_active = $pdo->query("SELECT COUNT(*) FROM pemeliharaan WHERE status IN ('Terjadwal', 'Sedang Dikerjakan')")->fetchColumn();
} catch (Exception $e) {
    $count_kalibrasi_alert = 0;
    $count_maint_active = 0;
}
?>
<!-- Sidebar -->
<aside class="app-sidebar">
    <div class="sidebar-header">
        <a href="<?= BASE_URL; ?>/index.php" class="sidebar-brand">
            <div class="logo-icon">
                <i class="fas fa-heart-pulse heartbeat"></i>
            </div>
            <div>
                <span class="d-block text-white" style="line-height: 1.15; font-weight: 800; font-size: 1.1rem; letter-spacing: -0.02em;">CardioCare</span>
                <small class="text-xs text-uppercase fw-semibold" style="font-size: 0.62rem; letter-spacing: 0.08em; color: var(--accent);">SIMRS Elektromedis</small>
            </div>
        </a>
    </div>

    <div class="sidebar-menu">
        <div class="menu-category">Menu Utama</div>
        <a href="<?= BASE_URL; ?>/index.php" class="nav-link-custom <?= ($current_page == 'index.php' && $current_dir == 'crud') ? 'active' : ''; ?>">
            <i class="fas fa-gauge-high"></i>
            <span>Executive Dashboard</span>
        </a>

        <div class="menu-category mt-3">Manajemen Fasilitas</div>
        <a href="<?= BASE_URL; ?>/inventaris/index.php" class="nav-link-custom <?= ($current_dir == 'inventaris') ? 'active' : ''; ?>">
            <i class="fas fa-boxes-stacked"></i>
            <span>Inventaris Alat Medis</span>
        </a>
        <a href="<?= BASE_URL; ?>/pemeliharaan/index.php" class="nav-link-custom <?= ($current_dir == 'pemeliharaan') ? 'active' : ''; ?>">
            <i class="fas fa-screwdriver-wrench"></i>
            <span class="flex-grow-1">Pemeliharaan & Servis</span>
            <?php if ($count_maint_active > 0): ?>
                <span class="badge bg-warning text-dark rounded-pill px-2"><?= $count_maint_active; ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= BASE_URL; ?>/kalibrasi/index.php" class="nav-link-custom <?= ($current_dir == 'kalibrasi') ? 'active' : ''; ?>">
            <i class="fas fa-award"></i>
            <span class="flex-grow-1">Uji Kalibrasi & Laik</span>
            <?php if ($count_kalibrasi_alert > 0): ?>
                <span class="badge bg-danger text-white rounded-pill px-2"><?= $count_kalibrasi_alert; ?></span>
            <?php endif; ?>
        </a>

        <div class="menu-category mt-3">Rekapitulasi</div>
        <a href="<?= BASE_URL; ?>/laporan/index.php" class="nav-link-custom <?= ($current_dir == 'laporan') ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice"></i>
            <span>Pusat Cetak & Laporan</span>
        </a>

        <div class="menu-category mt-3">Portal Website</div>
        <a href="<?= BASE_URL; ?>/index.html" target="_blank" class="nav-link-custom text-info">
            <i class="fas fa-earth-americas"></i>
            <span class="flex-grow-1">Landing Page Publik</span>
            <i class="fas fa-arrow-up-right-from-square small opacity-50"></i>
        </a>

        <div class="menu-category mt-3">Akun & Sistem</div>
        <a href="<?= BASE_URL; ?>/auth/profile.php" class="nav-link-custom <?= ($current_page == 'profile.php') ? 'active' : ''; ?>">
            <i class="fas fa-user-gear"></i>
            <span>Profil & Keamanan</span>
        </a>
        <a href="<?= BASE_URL; ?>/auth/logout.php" class="nav-link-custom text-danger hover-danger">
            <i class="fas fa-right-from-bracket"></i>
            <span>Keluar (Sign Out)</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="user-profile-badge">
            <div class="avatar-circle">
                <?= strtoupper(substr($user['nama_lengkap'] ?? 'U', 0, 1)); ?>
            </div>
            <div class="text-truncate">
                <div class="text-white fw-bold small text-truncate d-flex align-items-center gap-1.5">
                    <span class="pulse-dot-success"></span>
                    <span><?= htmlspecialchars($user['nama_lengkap'] ?? 'User'); ?></span>
                </div>
                <div class="text-muted text-xs text-uppercase" style="font-size: 0.68rem;">
                    <span class="badge bg-white bg-opacity-10 text-white rounded-pill px-2">
                        <?= strtoupper($user['role'] ?? 'Admin'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</aside>
