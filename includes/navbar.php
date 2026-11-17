<?php
// Query quick notification alerts
try {
    global $pdo;
    $expiring_calibrations = $pdo->query("
        SELECT k.*, a.nama_alat, a.kode_alat 
        FROM kalibrasi k 
        JOIN alat a ON k.id_alat = a.id 
        WHERE k.tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
        ORDER BY k.tanggal_kadaluarsa ASC 
        LIMIT 5
    ")->fetchAll();
    
    $pending_maintenances = $pdo->query("
        SELECT p.*, a.nama_alat, a.kode_alat 
        FROM pemeliharaan p 
        JOIN alat a ON p.id_alat = a.id 
        WHERE p.status IN ('Terjadwal', 'Sedang Dikerjakan') 
        ORDER BY p.tanggal_jadwal ASC 
        LIMIT 5
    ")->fetchAll();
    
    $total_notif = count($expiring_calibrations) + count($pending_maintenances);
} catch (Exception $e) {
    $expiring_calibrations = [];
    $pending_maintenances = [];
    $total_notif = 0;
}
?>
<!-- Main Content Container Starts -->
<div class="app-main">
    <!-- Navbar -->
    <header class="app-navbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light mobile-sidebar-toggle p-2 rounded-lg" id="sidebarToggle" type="button" aria-label="Toggle Sidebar">
                <i class="fas fa-bars fs-5"></i>
            </button>
            <div class="d-none d-md-block">
                <div class="d-flex align-items-center gap-2">
                    <span class="pulse-dot"></span>
                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($page_title ?? 'Dashboard'); ?></h6>
                </div>
                <small class="text-muted" style="font-size: 0.75rem;">CardioCare SIMRS &bull; Manajemen Fasilitas Elektromedis</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 gap-sm-3">
            <!-- Shortcut to Landing Page -->
            <a href="<?= BASE_URL; ?>/index.html" class="btn btn-sm btn-light border d-none d-lg-inline-flex align-items-center gap-2 rounded-pill px-3 shadow-xs" title="Buka Landing Page Publik CardioCare" target="_blank">
                <i class="fas fa-globe text-danger"></i>
                <span class="small fw-semibold">Web Portal</span>
                <i class="fas fa-arrow-up-right-from-square text-muted" style="font-size: 0.7rem;"></i>
            </a>

            <!-- Theme Switcher Dropdown -->
            <div class="dropdown">
                <button class="btn btn-light d-flex align-items-center gap-2 px-2.5 py-1.5 rounded-3 border" type="button" id="themeMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Pilih Tema Warna">
                    <i class="fas fa-palette text-primary"></i>
                    <span class="d-none d-xl-inline small fw-semibold" id="currentThemeName">CardioCare Red</span>
                    <i class="fas fa-chevron-down text-muted small ms-1" style="font-size: 0.65rem;"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end theme-dropdown-menu mt-2" aria-labelledby="themeMenuDropdown">
                    <div class="px-2 py-1 mb-1 text-muted small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                        Pilih Tema Tampilan
                    </div>
                    <a href="javascript:void(0)" class="theme-option" data-theme-value="cardiocare">
                        <span class="theme-color-dot cardiocare"></span>
                        <div class="flex-grow-1">
                            <div>CardioCare Red</div>
                            <small class="text-muted" style="font-size: 0.72rem;">Nuansa Medis Merah Crimson</small>
                        </div>
                    </a>
                    <a href="javascript:void(0)" class="theme-option" data-theme-value="dark">
                        <span class="theme-color-dot dark"></span>
                        <div class="flex-grow-1">
                            <div>Dark Mode (Midnight)</div>
                            <small class="text-muted" style="font-size: 0.72rem;">Mode Gelap Cyber Hospital</small>
                        </div>
                    </a>
                    <a href="javascript:void(0)" class="theme-option" data-theme-value="ocean">
                        <span class="theme-color-dot ocean"></span>
                        <div class="flex-grow-1">
                            <div>Hospital Ocean</div>
                            <small class="text-muted" style="font-size: 0.72rem;">Biru Klasik Klinis</small>
                        </div>
                    </a>
                    <a href="javascript:void(0)" class="theme-option" data-theme-value="emerald">
                        <span class="theme-color-dot emerald"></span>
                        <div class="flex-grow-1">
                            <div>Health Emerald</div>
                            <small class="text-muted" style="font-size: 0.72rem;">Hijau Farmasi & Kesehatan</small>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Quick Dark Mode Toggle -->
            <button class="btn btn-light btn-theme-toggle p-2 rounded-circle" id="btnThemeToggle" type="button" title="Beralih Cepat Gelap/Terang">
                <i class="fas fa-moon text-secondary"></i>
            </button>

            <!-- Notifications Dropdown -->
            <div class="dropdown">
                <button class="btn btn-light position-relative p-2 rounded-circle" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="width: 40px; height: 40px;">
                    <i class="far fa-bell text-secondary"></i>
                    <?php if ($total_notif > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 0.65rem;">
                            <?= $total_notif; ?>
                        </span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 mt-2" aria-labelledby="notifDropdown" style="width: 330px;">
                    <div class="p-3 bg-light border-bottom rounded-top-4 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark"><i class="fas fa-bell text-danger me-2"></i>Notifikasi & Peringatan</span>
                        <span class="badge bg-danger rounded-pill"><?= $total_notif; ?> Baru</span>
                    </div>
                    <div class="p-2" style="max-height: 300px; overflow-y: auto;">
                        <?php if ($total_notif === 0): ?>
                            <div class="text-center py-4 text-muted small">
                                <i class="fas fa-check-circle text-success fs-3 mb-2 d-block"></i>
                                Semua aman! Tidak ada peringatan jatuh tempo.
                            </div>
                        <?php else: ?>
                            <?php foreach ($expiring_calibrations as $c): ?>
                                <a href="<?= BASE_URL; ?>/kalibrasi/index.php" class="dropdown-item p-2 rounded-3 text-wrap mb-1 d-flex gap-2 align-items-start">
                                    <div class="text-danger mt-1"><i class="fas fa-calendar-times"></i></div>
                                    <div style="font-size: 0.82rem;">
                                        <div class="fw-semibold text-truncate" style="max-width: 230px;"><?= htmlspecialchars($c['nama_alat']); ?></div>
                                        <div class="text-danger small">Jatuh tempo kalibrasi: <?= date('d M Y', strtotime($c['tanggal_kadaluarsa'])); ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                            <?php foreach ($pending_maintenances as $p): ?>
                                <a href="<?= BASE_URL; ?>/pemeliharaan/index.php" class="dropdown-item p-2 rounded-3 text-wrap mb-1 d-flex gap-2 align-items-start">
                                    <div class="text-warning mt-1"><i class="fas fa-tools"></i></div>
                                    <div style="font-size: 0.82rem;">
                                        <div class="fw-semibold text-truncate" style="max-width: 230px;"><?= htmlspecialchars($p['nama_alat']); ?></div>
                                        <div class="text-muted small">Servis: <?= htmlspecialchars($p['status']); ?> (<?= htmlspecialchars($p['teknisi']); ?>)</div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- User Menu Dropdown -->
            <div class="dropdown">
                <button class="btn btn-light d-flex align-items-center gap-2 border-0 bg-transparent p-1" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="avatar-circle">
                        <?= strtoupper(substr($user['nama_lengkap'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div class="text-start d-none d-sm-block">
                        <div class="fw-bold small text-truncate" style="max-width: 140px;"><?= htmlspecialchars($user['nama_lengkap'] ?? 'Pengguna'); ?></div>
                        <div class="text-muted text-capitalize" style="font-size: 0.7rem;"><?= htmlspecialchars($user['role'] ?? 'Admin'); ?></div>
                    </div>
                    <i class="fas fa-chevron-down text-muted small ms-1"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 py-2 mt-2" aria-labelledby="userMenuDropdown" style="min-width: 210px;">
                    <li class="px-3 py-2 border-bottom mb-1">
                        <span class="d-block fw-semibold small"><?= htmlspecialchars($user['nama_lengkap'] ?? ''); ?></span>
                        <span class="d-block text-muted text-xs"><?= htmlspecialchars($user['email'] ?? ''); ?></span>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 small" href="<?= BASE_URL; ?>/auth/profile.php">
                            <i class="fas fa-user-circle me-2 text-primary"></i> Edit Profil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 small" href="<?= BASE_URL; ?>/auth/profile.php#security">
                            <i class="fas fa-key me-2 text-warning"></i> Ganti Password
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item py-2 small text-danger" href="<?= BASE_URL; ?>/auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Keluar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Page Content Container -->
    <main class="page-container">
        <!-- Flash Alert Message if any -->
        <?= display_flash(); ?>
