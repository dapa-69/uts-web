<?php
/**
 * Cetak Laporan Master Inventaris Alat Medis
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helper.php';

require_login();

$stmt = $pdo->query("SELECT * FROM alat ORDER BY lokasi ASC, nama_alat ASC");
$alat_list = $stmt->fetchAll();

$total_unit = count($alat_list);
$total_aset = array_sum(array_column($alat_list, 'harga_pengadaan'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inventaris Alat Medis - <?= date('d/m/Y'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Times New Roman', Times, serif; color: #000; background: #fff; padding: 25px; }
        .kop-surat { border-bottom: 3px double #000; padding-bottom: 12px; margin-bottom: 25px; }
        table { font-size: 12px; }
        th { background-color: #f0f0f0 !important; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print mb-4 text-end">
    <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i> Cetak Laporan</button>
    <button onclick="window.close()" class="btn btn-secondary btn-sm"><i class="fas fa-times me-1"></i> Tutup</button>
</div>

<div class="kop-surat text-center">
    <h5 class="mb-0 fw-bold">RUMAH SAKIT UMUM &bull; INSTALASI ELEKTROMEDIS</h5>
    <h4 class="mb-1 fw-bold">LAPORAN MASTER DATA INVENTARIS ALAT ELEKTROMEDIS</h4>
    <small>Per Tanggal: <?= format_tanggal(date('Y-m-d')); ?> | Dicetak Oleh: <?= htmlspecialchars($_SESSION['user_data']['nama_lengkap'] ?? 'Administrator'); ?></small>
</div>

<div class="d-flex justify-content-between mb-3 small">
    <div><strong>Total Alat Terdata:</strong> <?= $total_unit; ?> Unit</div>
    <div><strong>Total Nilai Perolehan Aset:</strong> <?= format_rupiah($total_aset); ?></div>
</div>

<table class="table table-bordered table-sm align-middle">
    <thead>
        <tr class="text-center align-middle">
            <th width="4%">No</th>
            <th>Kode Alat</th>
            <th>Nama Alat</th>
            <th>Kategori</th>
            <th>Merek &amp; Model</th>
            <th>No. Seri (SN)</th>
            <th>Lokasi Ruangan</th>
            <th>Tahun</th>
            <th>Kondisi</th>
            <th>Status Operasional</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        foreach ($alat_list as $row): 
        ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td class="font-monospace fw-bold text-center"><?= htmlspecialchars($row['kode_alat']); ?></td>
                <td><strong><?= htmlspecialchars($row['nama_alat']); ?></strong></td>
                <td><?= htmlspecialchars($row['kategori']); ?></td>
                <td><?= htmlspecialchars($row['merek']); ?> <?= !empty($row['tipe_model']) ? ' - ' . htmlspecialchars($row['tipe_model']) : ''; ?></td>
                <td class="font-monospace small"><?= htmlspecialchars($row['nomor_seri'] ?: '-'); ?></td>
                <td><?= htmlspecialchars($row['lokasi']); ?></td>
                <td class="text-center"><?= $row['tahun']; ?></td>
                <td class="text-center"><?= htmlspecialchars($row['kondisi']); ?></td>
                <td class="text-center"><?= htmlspecialchars($row['status_operasional']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="row mt-5 text-center small">
    <div class="col-6">
        <p class="mb-5">Petugas Inventarisasi / IPSRS,</p>
        <p class="mb-0 fw-bold"><u><?= htmlspecialchars($_SESSION['user_data']['nama_lengkap'] ?? 'Petugas Elektromedis'); ?></u></p>
        <span class="text-muted">Staf Pengelola Fasilitas Medis</span>
    </div>
    <div class="col-6">
        <p class="mb-5">Mengetahui,<br>Kepala Bagian Sarana &amp; Prasarana,</p>
        <p class="mb-0 fw-bold"><u>Ir. H. Gunawan, M.T.</u></p>
        <span class="text-muted">NIP: 19820514 200801 1 003</span>
    </div>
</div>

</body>
</html>
