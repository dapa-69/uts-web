<?php
/**
 * Professional Registration Page - CardioCare SIMRS Pro
 */
$is_public_page = true;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helper.php';

// Jika sudah login, redirect langsung
if (is_logged_in()) {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username = strtolower(trim($_POST['username'] ?? ''));
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $role = $_POST['role'] ?? 'teknisi';

    if (empty($nama_lengkap) || empty($username) || empty($email) || empty($password)) {
        $error = 'Semua bidang formulir wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format alamat email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal harus 6 karakter.';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi kata sandi tidak cocok.';
    } elseif (!in_array($role, ['admin', 'teknisi', 'operator'])) {
        $role = 'teknisi';
    } else {
        try {
            // Cek apakah username atau email sudah ada
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = 'Username atau Email sudah terdaftar. Silakan gunakan yang lain.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nama_lengkap, $username, $email, $hashed_password, $role]);

                set_flash('success', 'Akun berhasil didaftarkan! Silakan masuk dengan kredensial baru Anda.');
                header("Location: " . BASE_URL . "/auth/login.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun - CardioCare SIMRS</title>
    
    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL; ?>/assets/heart.png" type="image/png">

    <!-- Theme Early Init -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('simrs_theme') || 'cardiocare';
                document.documentElement.setAttribute('data-theme', savedTheme);
            } catch(e) {}
        })();
    </script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6.5.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom Theme CSS -->
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>
<body class="auth-page d-flex align-items-center justify-content-center p-3 p-md-4">

<!-- Top Navigation Controls -->
<div class="auth-top-nav">
    <a href="<?= BASE_URL; ?>/index.html" class="btn btn-sm btn-light border text-danger rounded-pill px-3 shadow-sm d-flex align-items-center gap-1.5" title="Kembali ke Landing Page">
        <i class="fas fa-heart-pulse heartbeat"></i>
        <span class="small fw-semibold">Website Utama</span>
    </a>
    <button class="btn btn-sm btn-light border rounded-circle btn-theme-toggle" id="btnThemeToggle" type="button" title="Mode Gelap / Terang">
        <i class="fas fa-moon text-secondary"></i>
    </button>
</div>

<div class="container" style="max-width: 660px; z-index: 2;">
    <div class="auth-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex bg-danger bg-opacity-10 text-danger rounded-4 align-items-center justify-content-center mb-2" style="width: 52px; height: 52px; font-size: 1.5rem;">
                <i class="fas fa-heart-pulse heartbeat"></i>
            </div>
            <h3 class="fw-bold mb-1 font-heading">Daftar Akun Baru</h3>
            <p class="text-muted small">Registrasi staf atau teknisi untuk sistem manajemen alat CardioCare SIMRS</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center rounded-3 p-3 mb-4 shadow-sm">
                <i class="fas fa-circle-exclamation fs-5 me-3 text-danger"></i>
                <div class="small fw-medium"><?= htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL; ?>/auth/register.php" method="POST">
            <?= csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label">Nama Lengkap &amp; Gelar</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-muted"><i class="far fa-id-card"></i></span>
                    <input type="text" class="form-control" name="nama_lengkap" placeholder="Contoh: Budi Santoso, S.Tr.EM" value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent text-muted"><i class="far fa-user"></i></span>
                        <input type="text" class="form-control" name="username" placeholder="username unik" value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role / Hak Akses</label>
                    <select class="form-select" name="role" required>
                        <option value="teknisi" <?= (($_POST['role'] ?? '') == 'teknisi') ? 'selected' : ''; ?>>Teknisi Elektromedis</option>
                        <option value="admin" <?= (($_POST['role'] ?? '') == 'admin') ? 'selected' : ''; ?>>Administrator SIMRS</option>
                        <option value="operator" <?= (($_POST['role'] ?? '') == 'operator') ? 'selected' : ''; ?>>Operator / Perawat Ruangan</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat Email Resmi</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-muted"><i class="far fa-envelope"></i></span>
                    <input type="email" class="form-control" name="email" placeholder="nama@instansi.local" value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent text-muted"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="password" id="regPass" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent text-muted"><i class="fas fa-check"></i></span>
                        <input type="password" class="form-control" name="password_confirm" id="regPassConfirm" placeholder="Ulangi kata sandi" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 mb-3">
                <i class="fas fa-user-check me-2"></i>Daftarkan Akun
            </button>

            <div class="text-center pt-2 border-top">
                <small class="text-muted">
                    Sudah punya akun? 
                    <a href="<?= BASE_URL; ?>/auth/login.php" class="fw-semibold">Masuk Sekarang</a>
                </small>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/app.js"></script>
</body>
</html>
