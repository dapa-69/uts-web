<?php
/**
 * Helper Functions & Formatting
 */

/**
 * Format Angka ke Rupiah
 */
function format_rupiah($angka): string {
    if ($angka === null || $angka === '') return 'Rp 0';
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

/**
 * Format Tanggal ke Bahasa Indonesia (Contoh: 17 November 2026)
 */
function format_tanggal(?string $date, bool $with_time = false): string {
    if (empty($date) || $date === '0000-00-00') return '-';
    
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($date);
    if (!$timestamp) return '-';
    
    $tgl = date('j', $timestamp);
    $bln = $bulan[(int)date('n', $timestamp)];
    $thn = date('Y', $timestamp);
    
    $result = "{$tgl} {$bln} {$thn}";
    if ($with_time) {
        $result .= ' ' . date('H:i', $timestamp) . ' WIB';
    }
    
    return $result;
}

/**
 * Badge Kondisi Alat
 */
function badge_kondisi(string $kondisi): string {
    return match($kondisi) {
        'Baik' => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill"><i class="fas fa-check-circle me-1"></i> Baik</span>',
        'Rusak Ringan' => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1.5 rounded-pill"><i class="fas fa-exclamation-triangle me-1"></i> Rusak Ringan</span>',
        'Rusak Berat' => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 rounded-pill"><i class="fas fa-times-circle me-1"></i> Rusak Berat</span>',
        default => '<span class="badge bg-secondary-subtle text-secondary px-2.5 py-1.5 rounded-pill">' . htmlspecialchars($kondisi) . '</span>'
    };
}

/**
 * Badge Status Operasional Alat
 */
function badge_status_operasional(string $status): string {
    return match($status) {
        'Siap Pakai' => '<span class="badge bg-success text-white px-2.5 py-1.5 rounded-pill"><i class="fas fa-power-off me-1"></i> Siap Pakai</span>',
        'Dalam Pemeliharaan' => '<span class="badge bg-warning text-dark px-2.5 py-1.5 rounded-pill"><i class="fas fa-tools me-1"></i> Dalam Pemeliharaan</span>',
        'Dalam Kalibrasi' => '<span class="badge bg-info text-white px-2.5 py-1.5 rounded-pill"><i class="fas fa-tachometer-alt me-1"></i> Dalam Kalibrasi</span>',
        'Rusak' => '<span class="badge bg-danger text-white px-2.5 py-1.5 rounded-pill"><i class="fas fa-ban me-1"></i> Rusak</span>',
        'Afkir' => '<span class="badge bg-secondary text-white px-2.5 py-1.5 rounded-pill"><i class="fas fa-archive me-1"></i> Afkir</span>',
        default => '<span class="badge bg-light text-dark px-2.5 py-1.5 rounded-pill">' . htmlspecialchars($status) . '</span>'
    };
}

/**
 * Badge Status Pemeliharaan
 */
function badge_status_pemeliharaan(string $status): string {
    return match($status) {
        'Terjadwal' => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 rounded-pill"><i class="far fa-calendar-alt me-1"></i> Terjadwal</span>',
        'Sedang Dikerjakan' => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1.5 rounded-pill"><i class="fas fa-spinner fa-spin me-1"></i> Sedang Dikerjakan</span>',
        'Selesai' => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill"><i class="fas fa-check-double me-1"></i> Selesai</span>',
        'Dibatalkan' => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 rounded-pill"><i class="fas fa-times me-1"></i> Dibatalkan</span>',
        default => '<span class="badge bg-secondary-subtle text-secondary px-2.5 py-1.5 rounded-pill">' . htmlspecialchars($status) . '</span>'
    };
}

/**
 * Badge Hasil Kalibrasi
 */
function badge_hasil_kalibrasi(string $hasil): string {
    return match($hasil) {
        'Laik Pakai' => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill"><i class="fas fa-shield-alt me-1"></i> Laik Pakai</span>',
        'Tidak Laik Pakai' => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 rounded-pill"><i class="fas fa-exclamation-circle me-1"></i> Tidak Laik Pakai</span>',
        default => '<span class="badge bg-secondary-subtle text-secondary px-2.5 py-1.5 rounded-pill">' . htmlspecialchars($hasil) . '</span>'
    };
}

/**
 * Hitung status kadaluarsa kalibrasi
 */
function get_status_kadaluarsa_kalibrasi(string $tanggal_kadaluarsa): array {
    if (empty($tanggal_kadaluarsa)) {
        return ['status' => 'Belum Dikalibrasi', 'badge' => '<span class="badge bg-secondary">Belum Dikalibrasi</span>', 'days' => 0];
    }
    
    $today = new DateTime('today');
    $exp = new DateTime($tanggal_kadaluarsa);
    $diff = $today->diff($exp);
    $days = (int)$diff->format("%r%a");
    
    if ($days < 0) {
        return [
            'status' => 'Kadaluarsa',
            'days' => $days,
            'badge' => '<span class="badge bg-danger text-white px-2 py-1 rounded-pill"><i class="fas fa-exclamation-circle me-1"></i> Kadaluarsa (' . abs($days) . ' hari lalu)</span>'
        ];
    } elseif ($days <= 30) {
        return [
            'status' => 'Mendekati',
            'days' => $days,
            'badge' => '<span class="badge bg-warning text-dark px-2 py-1 rounded-pill"><i class="fas fa-clock me-1"></i> Jatuh Tempo dlm ' . $days . ' hari</span>'
        ];
    } else {
        return [
            'status' => 'Berlaku',
            'days' => $days,
            'badge' => '<span class="badge bg-success text-white px-2 py-1 rounded-pill"><i class="fas fa-check-circle me-1"></i> Masih Berlaku (' . $days . ' hari)</span>'
        ];
    }
}

/**
 * Set flash message
 */
function set_flash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Tampilkan Flash Message HTML
 */
function display_flash(): string {
    $html = '';
    $types = [
        'success' => ['class' => 'alert-success', 'icon' => 'fa-check-circle'],
        'error' => ['class' => 'alert-danger', 'icon' => 'fa-exclamation-circle'],
        'warning' => ['class' => 'alert-warning', 'icon' => 'fa-exclamation-triangle'],
        'info' => ['class' => 'alert-info', 'icon' => 'fa-info-circle'],
    ];

    foreach ($types as $type => $info) {
        $key = 'flash_' . $type;
        if (!empty($_SESSION[$key])) {
            $msg = htmlspecialchars($_SESSION[$key]);
            $html .= "
            <div class='alert {$info['class']} alert-dismissible fade show d-flex align-items-center shadow-sm rounded-3 my-3' role='alert'>
                <i class='fas {$info['icon']} fs-4 me-3'></i>
                <div class='flex-grow-1 font-medium'>{$msg}</div>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
            unset($_SESSION[$key]);
        }
    }
    return $html;
}

/**
 * Generator Kode Alat Otomatis
 */
function generate_kode_alat(PDO $pdo): string {
    $year = date('Y');
    $prefix = "ALT-{$year}-";
    $stmt = $pdo->prepare("SELECT kode_alat FROM alat WHERE kode_alat LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();
    
    if ($last) {
        $num = (int)substr($last, strlen($prefix)) + 1;
    } else {
        $num = 1;
    }
    return $prefix . str_pad((string)$num, 3, '0', STR_PAD_LEFT);
}

/**
 * Generator Kode Pemeliharaan Otomatis
 */
function generate_kode_pemeliharaan(PDO $pdo): string {
    $year = date('Y');
    $prefix = "MNT-{$year}-";
    $stmt = $pdo->prepare("SELECT kode_pemeliharaan FROM pemeliharaan WHERE kode_pemeliharaan LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();
    
    if ($last) {
        $num = (int)substr($last, strlen($prefix)) + 1;
    } else {
        $num = 1;
    }
    return $prefix . str_pad((string)$num, 3, '0', STR_PAD_LEFT);
}
