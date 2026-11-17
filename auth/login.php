<?php
/**
 * Professional Login Page - CardioCare SIMRS Pro
 */
$is_public_page = true;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helper.php';

// Jika sudah login, redirect langsung ke dashboard
if (is_logged_in()) {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$error = '';
$redirect_url = $_GET['redirect'] ?? (BASE_URL . '/index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $username_or_email = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username_or_email) || empty($password)) {
        $error = 'Silakan masukkan username/email dan password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username_or_email, $username_or_email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Regenerasi session ID untuk cegah session fixation
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_data'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'nama_lengkap' => $user['nama_lengkap'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'foto' => $user['foto']
                ];
                
                set_flash('success', 'Selamat datang kembali, ' . htmlspecialchars($user['nama_lengkap']) . '!');
                
                // Sanitasi redirect URL
                if (filter_var($redirect_url, FILTER_VALIDATE_URL) && str_contains($redirect_url, $_SERVER['HTTP_HOST'])) {
                    header("Location: " . $redirect_url);
                } else {
                    header("Location: " . BASE_URL . "/index.php");
                }
                exit;
            } else {
                $error = 'Username/email atau kata sandi yang Anda masukkan salah.';
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
    <title>Masuk - CardioCare SIMRS Manajemen Alat Medis</title>
    
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

<div class="container" style="max-width: 1050px; z-index: 2;">
    <div class="auth-card row g-0">
        <!-- Sisi Kiri: Branding & Fitur -->
        <div class="col-lg-6 auth-brand-side d-flex flex-column justify-content-between">
            <div style="z-index: 1;">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-danger text-white rounded-4 d-flex align-items-center justify-content-center shadow-lg" style="width: 48px; height: 48px; font-size: 1.5rem; background: var(--primary-gradient);">
                        <i class="fas fa-heart-pulse heartbeat"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold tracking-tight text-white font-heading">CardioCare SIMRS</h4>
                        <small class="text-white-50 text-uppercase fw-semibold" style="letter-spacing: 0.08em; font-size: 0.72rem;">Medical Asset & Calibration System</small>
                    </div>
                </div>
                
                <h2 class="fw-bold mb-3 text-white font-heading" style="line-height: 1.3;">Sistem Terintegrasi Fasilitas &amp; Alat Medis</h2>
                <p class="text-white-50 small mb-4" style="line-height: 1.6;">
                    Kelola siklus hidup aset rumah sakit mulai dari pencatatan inventaris, log pemeliharaan preventif, hingga sertifikasi kalibrasi terstandar.
                </p>
                
                <div class="d-flex flex-column gap-3 mt-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fas fa-boxes-stacked text-white small"></i>
                        </div>
                        <div>
                            <div class="text-white fw-semibold small">Inventarisasi Alat Lengkap</div>
                            <small class="text-white-50" style="font-size: 0.75rem;">Pelacakan nomor seri, lokasi ruangan, &amp; kondisi alat</small>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fas fa-screwdriver-wrench text-white small"></i>
                        </div>
                        <div>
                            <div class="text-white fw-semibold small">Pemeliharaan &amp; Service Log</div>
                            <small class="text-white-50" style="font-size: 0.75rem;">Preventif, korektif, suku cadang &amp; berita acara teknisi</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fas fa-award text-white small"></i>
                        </div>
                        <div>
                            <div class="text-white fw-semibold small">Pengawasan Kalibrasi &amp; Uji Laik</div>
                            <small class="text-white-50" style="font-size: 0.75rem;">Peringatan otomatis sebelum masa kalibrasi kadaluarsa</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 text-white-50 small" style="z-index: 1;">
                &copy; <?= date('Y'); ?> CardioCare SIMRS Pro. Standar Akreditasi Kemenkes RI.
            </div>
        </div>

        <!-- Sisi Kanan: Form Login -->
        <div class="col-lg-6 auth-form-side">
            <div class="mb-4">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill mb-2 fw-semibold">
                    <i class="fas fa-shield-halved me-1"></i> Portal Masuk Terproteksi
                </span>
                <h3 class="fw-bold mb-1 font-heading">Selamat Datang</h3>
                <p class="text-muted small">Masukkan kredensial akun Anda untuk mengakses dashboard manajemen.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger d-flex align-items-center rounded-3 p-3 mb-4 shadow-sm" role="alert">
                    <i class="fas fa-circle-exclamation fs-5 me-3 text-danger"></i>
                    <div class="small fw-medium"><?= htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <?= display_flash(); ?>

            <form action="<?= BASE_URL; ?>/auth/login.php?redirect=<?= urlencode($redirect_url); ?>" method="POST">
                <?= csrf_field(); ?>

                <div class="mb-3">
                    <label class="form-label" for="username">Username atau Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent text-muted"><i class="far fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username atau email" value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0" for="password">Kata Sandi (Password)</label>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent text-muted"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan kata sandi" required>
                        <button class="input-group-text bg-transparent text-muted" type="button" onclick="togglePasswordVisibility('password', 'toggleEyeIcon')">
                            <i class="far fa-eye" id="toggleEyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" checked>
                        <label class="form-check-label small text-muted" for="remember">
                            Ingat sesi saya
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2.5 mb-3 d-flex align-items-center justify-content-center gap-2">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Masuk ke Dashboard</span>
                </button>
            </form>

            <!-- Demo Quick-fill Accounts -->
            <div class="p-3 rounded-4 mb-4" style="background: var(--table-hover); border: 1px dashed var(--border-color);">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <small class="fw-bold"><i class="fas fa-key text-warning me-1"></i> Akun Uji Coba Cepat:</small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="fillCredentials('admin', 'admin123')">
                        <i class="fas fa-user-shield me-1"></i> Admin (admin / admin123)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="fillCredentials('teknisi', 'admin123')">
                        <i class="fas fa-wrench me-1"></i> Teknisi (teknisi / admin123)
                    </button>
                </div>
            </div>

            <div class="text-center pt-2 border-top">
                <small class="text-muted">
                    Belum memiliki akun terdaftar? 
                    <a href="<?= BASE_URL; ?>/auth/register.php" class="fw-semibold">Daftar Akun Baru</a>
                </small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/app.js"></script>
<script>
function fillCredentials(user, pass) {
    document.getElementById('username').value = user;
    document.getElementById('password').value = pass;
}
</script>
</body>
</html>
