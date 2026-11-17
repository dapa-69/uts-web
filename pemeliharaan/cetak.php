<?php
/**
 * Cetak Lembar Berita Acara Pemeliharaan (BAP / SPK)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helper.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT p.*, a.nama_alat, a.kode_alat, a.merek, a.tipe_model, a.nomor_seri, a.lokasi, a.kategori 
    FROM pemeliharaan p 
    JOIN alat a ON p.id_alat = a.id 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$maint = $stmt->fetch();

if (!$maint) {
    die("Catatan pemeliharaan tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Servis - <?= htmlspecialchars($maint['kode_pemeliharaan']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Times New Roman', Times, serif; color: #000; background: #fff; padding: 25px; }
        .kop-surat { border-bottom: 3px double #000; padding-bottom: 12px; margin-bottom: 25px; }
        .table-data th, .table-data td { padding: 6px 10px; font-size: 13px; }
        .box-section { border: 1px solid #000; padding: 12px; margin-bottom: 15px; border-radius: 4px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print mb-4 text-end">
    <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i> Cetak Dokumen</button>
    <button onclick="window.close()" class="btn btn-secondary btn-sm"><i class="fas fa-times me-1"></i> Tutup</button>
</div>

<!-- Kop Surat -->
<div class="kop-surat text-center">
    <h5 class="mb-0 fw-bold">RUMAH SAKIT UMUM &bull; INSTALASI PEMELIHARAAN SARANA (IPSRS)</h5>
    <h4 class="mb-1 fw-bold">BERITA ACARA PEMELIHARAAN &amp; PERBAIKAN ALAT MEDIS</h4>
    <small>Jl. Kesehatan Medika No. 10 | Telp: (021) 555-0199 | Email: ipsrs@medika.local</small>
</div>

<div class="d-flex justify-content-between mb-3 small">
    <div><strong>Nomor Dokumen:</strong> <?= htmlspecialchars($maint['kode_pemeliharaan']); ?></div>
    <div><strong>Tanggal:</strong> <?= format_tanggal($maint['tanggal_selesai'] ?: $maint['tanggal_jadwal']); ?></div>
</div>

<!-- Informasi Alat -->
<div class="box-section">
    <h6 class="fw-bold mb-2 border-bottom pb-1" style="font-size: 14px;">I. IDENTITAS ALAT ELEKTROMEDIS</h6>
    <table class="w-100 table-data">
        <tr>
            <td width="20%"><strong>Kode Alat</strong></td>
            <td width="30%">: <?= htmlspecialchars($maint['kode_alat']); ?></td>
            <td width="20%"><strong>Merek / Tipe</strong></td>
            <td width="30%">: <?= htmlspecialchars($maint['merek']); ?> / <?= htmlspecialchars($maint['tipe_model'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td><strong>Nama Alat</strong></td>
            <td>: <?= htmlspecialchars($maint['nama_alat']); ?></td>
            <td><strong>Nomor Seri (SN)</strong></td>
            <td>: <?= htmlspecialchars($maint['nomor_seri'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td><strong>Ruangan / Lokasi</strong></td>
            <td>: <?= htmlspecialchars($maint['lokasi']); ?></td>
            <td><strong>Kategori</strong></td>
            <td>: <?= htmlspecialchars($maint['kategori']); ?></td>
        </tr>
    </table>
</div>

<!-- Uraian Pengerjaan -->
<div class="box-section">
    <h6 class="fw-bold mb-2 border-bottom pb-1" style="font-size: 14px;">II. URAIAN PEMELIHARAAN &amp; TINDAKAN</h6>
    <table class="w-100 table-data">
        <tr>
            <td width="25%"><strong>Jenis Pemeliharaan</strong></td>
            <td>: <?= htmlspecialchars($maint['jenis_pemeliharaan']); ?></td>
        </tr>
        <tr>
            <td><strong>Keluhan / Masalah Awal</strong></td>
            <td>: <?= htmlspecialchars($maint['keluhan'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td><strong>Tindakan yang Dilakukan</strong></td>
            <td>: <?= htmlspecialchars($maint['tindakan'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td><strong>Suku Cadang Diganti</strong></td>
            <td>: <?= htmlspecialchars($maint['suku_cadang'] ?: 'Tidak ada'); ?></td>
        </tr>
        <tr>
            <td><strong>Biaya Pemeliharaan</strong></td>
            <td>: <?= format_rupiah($maint['biaya']); ?></td>
        </tr>
        <tr>
            <td><strong>Status Pengerjaan Akhir</strong></td>
            <td>: <strong><?= htmlspecialchars($maint['status']); ?></strong></td>
        </tr>
        <tr>
            <td><strong>Catatan / Saran Teknisi</strong></td>
            <td>: <?= htmlspecialchars($maint['catatan'] ?: '-'); ?></td>
        </tr>
    </table>
</div>

<!-- Kolom Tanda Tangan -->
<div class="row mt-5 text-center small">
    <div class="col-4">
        <p class="mb-5">Pengguna Ruangan / Perawat,</p>
        <p class="mb-0 fw-bold"><u>( ......................................... )</u></p>
        <span class="text-muted">NIP: .................................</span>
    </div>
    <div class="col-4">
        <p class="mb-5">Teknisi Elektromedis Pelaksana,</p>
        <p class="mb-0 fw-bold"><u><?= htmlspecialchars($maint['teknisi']); ?></u></p>
        <span class="text-muted">Staf Teknisi IPSRS</span>
    </div>
    <div class="col-4">
        <p class="mb-5">Mengetahui,<br>Kepala Unit IPSRS,</p>
        <p class="mb-0 fw-bold"><u>Ir. H. Gunawan, M.T.</u></p>
        <span class="text-muted">NIP: 19820514 200801 1 003</span>
    </div>
</div>

</body>
</html>
