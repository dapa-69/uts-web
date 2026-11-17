<?php
/**
 * User Profile & Password Security
 */
$page_title = 'Profil & Keamanan Akun';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';

$current_user = current_user();
$msg_profile = '';
$err_profile = '';
$msg_pass = '';
$err_pass = '';

// Handle update profile info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_profile'])) {
    verify_csrf();
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));

    if (empty($nama_lengkap) || empty($email)) {
        $err_profile = 'Nama lengkap dan email tidak boleh kosong.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err_profile = 'Format email tidak valid.';
    } else {
        try {
            // Cek duplikasi email dengan user lain
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $current_user['id']]);
            if ($stmt->fetch()) {
                $err_profile = 'Alamat email ini sudah digunakan oleh akun lain.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ? WHERE id = ?");
                $stmt->execute([$nama_lengkap, $email, $current_user['id']]);
                $_SESSION['user_data']['nama_lengkap'] = $nama_lengkap;
                $_SESSION['user_data']['email'] = $email;
                $current_user = current_user();
                $msg_profile = 'Data profil berhasil diperbarui.';
            }
        } catch (PDOException $e) {
            $err_profile = 'Gagal memperbarui profil: ' . $e->getMessage();
        }
    }
}

// Handle change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_password'])) {
    verify_csrf();
    $old_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $conf_pass = $_POST['confirm_password'] ?? '';

    if (empty($old_pass) || empty($new_pass) || empty($conf_pass)) {
        $err_pass = 'Semua kolom kata sandi wajib diisi.';
    } elseif (strlen($new_pass) < 6) {
        $err_pass = 'Kata sandi baru minimal 6 karakter.';
    } elseif ($new_pass !== $conf_pass) {
        $err_pass = 'Konfirmasi kata sandi baru tidak sesuai.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$current_user['id']]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($old_pass, $hash)) {
                $err_pass = 'Kata sandi saat ini tidak cocok.';
            } else {
                $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$new_hash, $current_user['id']]);
                $msg_pass = 'Kata sandi berhasil diubah! Gunakan kata sandi baru untuk login berikutnya.';
            }
        } catch (PDOException $e) {
            $err_pass = 'Gagal mengganti kata sandi: ' . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Pengaturan Profil &amp; Keamanan</h4>
        <p class="text-muted small mb-0">Kelola identitas akun dan perlindungan kata sandi Anda.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Kartu Identitas Pengguna -->
    <div class="col-lg-4">
        <div class="card-custom text-center p-4">
            <div class="avatar-circle mx-auto mb-3 shadow" style="width: 80px; height: 80px; font-size: 2rem;">
                <?= strtoupper(substr($current_user['nama_lengkap'] ?? 'U', 0, 1)); ?>
            </div>
            <h5 class="fw-bold mb-1"><?= htmlspecialchars($current_user['nama_lengkap']); ?></h5>
            <p class="text-muted small mb-3">@<?= htmlspecialchars($current_user['username']); ?></p>
            
            <div class="mb-3">
                <span class="badge bg-primary px-3 py-2 rounded-pill text-uppercase">
                    <i class="fas fa-shield-alt me-1"></i> Role: <?= htmlspecialchars($current_user['role']); ?>
                </span>
            </div>

            <hr class="my-4">

            <div class="text-start small">
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Email</span>
                    <span class="fw-semibold text-dark"><?= htmlspecialchars($current_user['email']); ?></span>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Status Akun</span>
                    <span class="badge bg-success-subtle text-success">Aktif</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Form Edit Profil & Ganti Password -->
    <div class="col-lg-8">
        <!-- Card 1: Data Profil -->
        <div class="card-custom mb-4">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="fas fa-user-edit text-primary me-2"></i>Informasi Data Pengguna</h6>
            </div>
            <div class="card-body">
                <?php if ($msg_profile): ?>
                    <div class="alert alert-success d-flex align-items-center p-3 mb-3 rounded-3 small">
                        <i class="fas fa-check-circle fs-5 me-2"></i> <?= htmlspecialchars($msg_profile); ?>
                    </div>
                <?php endif; ?>
                <?php if ($err_profile): ?>
                    <div class="alert alert-danger d-flex align-items-center p-3 mb-3 rounded-3 small">
                        <i class="fas fa-exclamation-circle fs-5 me-2"></i> <?= htmlspecialchars($err_profile); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="action_profile" value="1">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Username (ID Akun)</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($current_user['username']); ?>" readonly disabled>
                            <small class="text-muted" style="font-size: 0.75rem;">Username tidak dapat diubah.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Hak Akses / Peran</label>
                            <input type="text" class="form-control bg-light text-capitalize" value="<?= htmlspecialchars($current_user['role']); ?>" readonly disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap &amp; Gelar</label>
                        <input type="text" class="form-control" name="nama_lengkap" value="<?= htmlspecialchars($current_user['nama_lengkap']); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Alamat Email</label>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($current_user['email']); ?>" required>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Card 2: Keamanan Kata Sandi -->
        <div class="card-custom" id="security">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="fas fa-lock text-warning me-2"></i>Ganti Kata Sandi (Password)</h6>
            </div>
            <div class="card-body">
                <?php if ($msg_pass): ?>
                    <div class="alert alert-success d-flex align-items-center p-3 mb-3 rounded-3 small">
                        <i class="fas fa-check-circle fs-5 me-2"></i> <?= htmlspecialchars($msg_pass); ?>
                    </div>
                <?php endif; ?>
                <?php if ($err_pass): ?>
                    <div class="alert alert-danger d-flex align-items-center p-3 mb-3 rounded-3 small">
                        <i class="fas fa-exclamation-circle fs-5 me-2"></i> <?= htmlspecialchars($err_pass); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="action_password" value="1">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kata Sandi Saat Ini</label>
                        <input type="password" class="form-control" name="current_password" required placeholder="Masukkan kata sandi lama">
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kata Sandi Baru</label>
                            <input type="password" class="form-control" name="new_password" required placeholder="Minimal 6 karakter">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" class="form-control" name="confirm_password" required placeholder="Ulangi kata sandi baru">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
                            <i class="fas fa-shield-alt me-1"></i> Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
