<?php
/**
 * Cetak Laporan Rekapitulasi Pemeliharaan
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helper.php';

require_login();

$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT p.*, a.nama_alat, a.kode_alat, a.lokasi 
    FROM pemeliharaan p 
    JOIN alat a ON p.id_alat = a.id 
    WHERE p.tanggal_jadwal BETWEEN ? AND ? 
    ORDER BY p.tanggal_jadwal ASC
");
$stmt->execute([$tgl_mulai, $tgl_selesai]);
$maint_list = $stmt->fetchAll();

$total_tugas = count($maint_list);
$total_biaya = array_sum(array_column($maint_list, 'biaya'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pemeliharaan Alat - <?= date('d/m/Y'); ?></title>
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
    <h4 class="mb-1 fw-bold">REKAPITULASI PEMELIHARAAN &amp; PERBAIKAN ALAT ELEKTROMEDIS</h4>
    <small>Periode: <?= format_tanggal($tgl_mulai); ?> s/d <?= format_tanggal($tgl_selesai); ?></small>
</div>

<div class="d-flex justify-content-between mb-3 small">
    <div><strong>Total Pemeliharaan:</strong> <?= $total_tugas; ?> Kegiatan</div>
    <div><strong>Total Pengeluaran Biaya:</strong> <?= format_rupiah($total_biaya); ?></div>
</div>

<table class="table table-bordered table-sm align-middle">
    <thead>
        <tr class="text-center align-middle">
            <th width="4%">No</th>
            <th>Kode SPK</th>
            <th>Nama Alat (Kode)</th>
            <th>Lokasi</th>
            <th>Jenis Servis</th>
            <th>Tgl Jadwal</th>
            <th>Tgl Selesai</th>
            <th>Teknisi</th>
            <th>Tindakan / Part</th>
            <th>Biaya (Rp)</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($maint_list)): ?>
            <tr>
                <td colspan="11" class="text-center py-3 text-muted">Tidak ada kegiatan pemeliharaan pada periode yang dipilih.</td>
            </tr>
        <?php else: ?>
            <?php 
            $no = 1;
            foreach ($maint_list as $row): 
            ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="font-monospace fw-bold text-center"><?= htmlspecialchars($row['kode_pemeliharaan']); ?></td>
                    <td>
                        <strong><?= htmlspecialchars($row['nama_alat']); ?></strong><br>
                        <small class="text-muted font-monospace"><?= htmlspecialchars($row['kode_alat']); ?></small>
                    </td>
                    <td><?= htmlspecialchars($row['lokasi']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['jenis_pemeliharaan']); ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal_jadwal'])); ?></td>
                    <td class="text-center"><?= !empty($row['tanggal_selesai']) ? date('d/m/Y', strtotime($row['tanggal_selesai'])) : '-'; ?></td>
                    <td><?= htmlspecialchars($row['teknisi']); ?></td>
                    <td class="small">
                        <?= htmlspecialchars($row['tindakan'] ?: $row['keluhan']); ?>
                        <?= !empty($row['suku_cadang']) ? '<br><em>(Part: ' . htmlspecialchars($row['suku_cadang']) . ')</em>' : ''; ?>
                    </td>
                    <td class="text-end fw-semibold"><?= format_rupiah($row['biaya']); ?></td>
                    <td class="text-center fw-bold"><?= htmlspecialchars($row['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="row mt-5 text-center small">
    <div class="col-6">
        <p class="mb-5">Koordinator Pemeliharaan Medis,</p>
        <p class="mb-0 fw-bold"><u>Ahmad Teknisi Elektromedis</u></p>
        <span class="text-muted">Penanggung Jawab Teknis</span>
    </div>
    <div class="col-6">
        <p class="mb-5">Mengetahui,<br>Kepala Bagian Sarana &amp; Prasarana,</p>
        <p class="mb-0 fw-bold"><u>Ir. H. Gunawan, M.T.</u></p>
        <span class="text-muted">NIP: 19820514 200801 1 003</span>
    </div>
</div>

</body>
</html>
