<?php
/**
 * Cetak Sertifikat Kalibrasi & Label Stiker Alat
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helper.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT k.*, a.nama_alat, a.kode_alat, a.merek, a.tipe_model, a.nomor_seri, a.lokasi, a.kategori 
    FROM kalibrasi k 
    JOIN alat a ON k.id_alat = a.id 
    WHERE k.id = ?
");
$stmt->execute([$id]);
$cal = $stmt->fetch();

if (!$cal) {
    die("Data kalibrasi tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Kalibrasi - <?= htmlspecialchars($cal['nomor_sertifikat']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Times New Roman', Times, serif; color: #000; background: #fff; padding: 25px; }
        .kop-surat { border-bottom: 3px double #000; padding-bottom: 12px; margin-bottom: 25px; }
        .table-data th, .table-data td { padding: 6px 10px; font-size: 13px; }
        .box-section { border: 1px solid #000; padding: 12px; margin-bottom: 15px; border-radius: 4px; }
        .sticker-box { border: 2px dashed #000; padding: 15px; width: 340px; margin: 20px auto; text-align: center; font-family: Arial, sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print mb-4 text-end">
    <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i> Cetak Dokumen &amp; Stiker</button>
    <button onclick="window.close()" class="btn btn-secondary btn-sm"><i class="fas fa-times me-1"></i> Tutup</button>
</div>

<!-- Kop Surat -->
<div class="kop-surat text-center">
    <h5 class="mb-0 fw-bold">RUMAH SAKIT UMUM &bull; INSTALASI ELEKTROMEDIS</h5>
    <h4 class="mb-1 fw-bold">LEMBAR PENGESAHAN UJI KALIBRASI &amp; KELAIKAN ALAT MEDIS</h4>
    <small>Berdasarkan Peraturan Menteri Kesehatan RI tentang Pengujian dan Kalibrasi Alat Kesehatan</small>
</div>

<div class="d-flex justify-content-between mb-3 small">
    <div><strong>Nomor Sertifikat:</strong> <?= htmlspecialchars($cal['nomor_sertifikat']); ?></div>
    <div><strong>Tanggal Pengesahan:</strong> <?= format_tanggal($cal['tanggal_kalibrasi']); ?></div>
</div>

<div class="box-section">
    <h6 class="fw-bold mb-2 border-bottom pb-1" style="font-size: 14px;">I. DATA IDENTITAS ALAT ELEKTROMEDIS</h6>
    <table class="w-100 table-data">
        <tr>
            <td width="22%"><strong>Kode Alat</strong></td>
            <td width="28%">: <?= htmlspecialchars($cal['kode_alat']); ?></td>
            <td width="22%"><strong>Merek / Tipe</strong></td>
            <td width="28%">: <?= htmlspecialchars($cal['merek']); ?> / <?= htmlspecialchars($cal['tipe_model'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td><strong>Nama Alat</strong></td>
            <td>: <?= htmlspecialchars($cal['nama_alat']); ?></td>
            <td><strong>Nomor Seri Pabrik</strong></td>
            <td>: <?= htmlspecialchars($cal['nomor_seri'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td><strong>Ruangan Penempatan</strong></td>
            <td>: <?= htmlspecialchars($cal['lokasi']); ?></td>
            <td><strong>Kategori Alat</strong></td>
            <td>: <?= htmlspecialchars($cal['kategori']); ?></td>
        </tr>
    </table>
</div>

<div class="box-section">
    <h6 class="fw-bold mb-2 border-bottom pb-1" style="font-size: 14px;">II. HASIL PENGUJIAN &amp; EVALUASI KELAIKAN</h6>
    <table class="w-100 table-data">
        <tr>
            <td width="30%"><strong>Lembaga Kalibrasi / Penguji</strong></td>
            <td>: <?= htmlspecialchars($cal['lembaga_kalibrasi']); ?></td>
        </tr>
        <tr>
            <td><strong>Tanggal Pelaksanaan Uji</strong></td>
            <td>: <?= format_tanggal($cal['tanggal_kalibrasi']); ?></td>
        </tr>
        <tr>
            <td><strong>Masa Berlaku s/d (Kadaluarsa)</strong></td>
            <td>: <strong><?= format_tanggal($cal['tanggal_kadaluarsa']); ?></strong></td>
        </tr>
        <tr>
            <td><strong>Hasil Keputusan Kelaikan</strong></td>
            <td>: <strong class="<?= ($cal['hasil'] === 'Laik Pakai') ? 'text-success' : 'text-danger'; ?>"><?= htmlspecialchars($cal['hasil']); ?></strong></td>
        </tr>
        <tr>
            <td><strong>Catatan Teknis &amp; Keselamatan</strong></td>
            <td>: <?= htmlspecialchars($cal['catatan'] ?: 'Lolos pengujian standar IEC 60601 dan toleransi fungsi parameter.'); ?></td>
        </tr>
    </table>
</div>

<!-- Simulasi Stiker Tempel -->
<div class="text-center my-4">
    <small class="text-muted d-block mb-1">--- Label Stiker Tempel pada Fisik Alat (Gunting pada garis putus-putus) ---</small>
    <div class="sticker-box">
        <div class="fw-bold fs-6 mb-1 text-uppercase">INSTALASI ELEKTROMEDIS</div>
        <div class="badge bg-dark text-white px-3 py-1 mb-2 fs-6">STATUS: <?= htmlspecialchars($cal['hasil']); ?></div>
        <div class="fw-bold text-dark small"><?= htmlspecialchars($cal['nama_alat']); ?></div>
        <div class="small text-muted font-monospace mb-1">No. Seri: <?= htmlspecialchars($cal['nomor_seri'] ?: '-'); ?></div>
        <div class="small fw-semibold border-top pt-1 text-danger">Wajib Kalibrasi Ulang: <?= date('d/m/Y', strtotime($cal['tanggal_kadaluarsa'])); ?></div>
        <div class="small text-muted" style="font-size: 11px;">Penguji: <?= htmlspecialchars($cal['lembaga_kalibrasi']); ?></div>
    </div>
</div>

<!-- Kolom Tanda Tangan -->
<div class="row mt-4 text-center small">
    <div class="col-6">
        <p class="mb-5">Petugas / Teknisi Kalibrator,</p>
        <p class="mb-0 fw-bold"><u>( ......................................... )</u></p>
        <span class="text-muted">Balai / Lembaga Penguji</span>
    </div>
    <div class="col-6">
        <p class="mb-5">Mengetahui &amp; Mengesahkan,<br>Kepala Unit Sarana Prasarana (IPSRS),</p>
        <p class="mb-0 fw-bold"><u>Ir. H. Gunawan, M.T.</u></p>
        <span class="text-muted">NIP: 19820514 200801 1 003</span>
    </div>
</div>

</body>
</html>
