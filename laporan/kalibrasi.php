<?php
/**
 * Cetak Laporan Rekapitulasi Kalibrasi & Uji Laik
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helper.php';

require_login();

$status_filter = $_GET['status_filter'] ?? '';

$query = "
    SELECT k.*, a.nama_alat, a.kode_alat, a.merek, a.lokasi, a.nomor_seri 
    FROM kalibrasi k 
    JOIN alat a ON k.id_alat = a.id 
    WHERE 1=1
";

if ($status_filter === 'kadaluarsa') {
    $query .= " AND k.tanggal_kadaluarsa < CURDATE()";
} elseif ($status_filter === 'mendekati') {
    $query .= " AND k.tanggal_kadaluarsa BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
} elseif ($status_filter === 'berlaku') {
    $query .= " AND k.tanggal_kadaluarsa > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
}

$query .= " ORDER BY k.tanggal_kadaluarsa ASC";
$stmt = $pdo->query($query);
$kalibrasi_list = $stmt->fetchAll();

$total_uji = count($kalibrasi_list);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Status Kalibrasi Alat Medis - <?= date('d/m/Y'); ?></title>
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
    <h4 class="mb-1 fw-bold">REKAPITULASI STATUS KALIBRASI &amp; KELAIKAN ALAT MEDIS</h4>
    <small>Standar Akreditasi KARS / Kemenkes RI | Tanggal Cetak: <?= format_tanggal(date('Y-m-d')); ?></small>
</div>

<div class="d-flex justify-content-between mb-3 small">
    <div><strong>Total Sertifikat:</strong> <?= $total_uji; ?> Instrumen</div>
    <div><strong>Filter:</strong> <?= !empty($status_filter) ? ucfirst($status_filter) : 'Semua Masa Berlaku'; ?></div>
</div>

<table class="table table-bordered table-sm align-middle">
    <thead>
        <tr class="text-center align-middle">
            <th width="4%">No</th>
            <th>No. Sertifikat</th>
            <th>Nama Alat (Kode)</th>
            <th>Merek / No. Seri</th>
            <th>Ruangan</th>
            <th>Lembaga Penguji</th>
            <th>Tgl Uji</th>
            <th>Jatuh Tempo</th>
            <th>Status Masa Berlaku</th>
            <th>Hasil Kelaikan</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($kalibrasi_list)): ?>
            <tr>
                <td colspan="10" class="text-center py-3 text-muted">Tidak ada data sertifikat kalibrasi yang sesuai kriteria.</td>
            </tr>
        <?php else: ?>
            <?php 
            $no = 1;
            foreach ($kalibrasi_list as $row): 
                $exp = get_status_kadaluarsa_kalibrasi($row['tanggal_kadaluarsa']);
            ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="font-monospace fw-bold text-center"><?= htmlspecialchars($row['nomor_sertifikat']); ?></td>
                    <td>
                        <strong><?= htmlspecialchars($row['nama_alat']); ?></strong><br>
                        <small class="text-muted font-monospace"><?= htmlspecialchars($row['kode_alat']); ?></small>
                    </td>
                    <td>
                        <?= htmlspecialchars($row['merek']); ?><br>
                        <small class="text-muted">SN: <?= htmlspecialchars($row['nomor_seri'] ?: '-'); ?></small>
                    </td>
                    <td><?= htmlspecialchars($row['lokasi']); ?></td>
                    <td><?= htmlspecialchars($row['lembaga_kalibrasi']); ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal_kalibrasi'])); ?></td>
                    <td class="text-center fw-bold"><?= date('d/m/Y', strtotime($row['tanggal_kadaluarsa'])); ?></td>
                    <td class="text-center">
                        <?php if ($exp['status'] === 'Kadaluarsa'): ?>
                            <strong class="text-danger">KADALUARSA</strong>
                        <?php elseif ($exp['status'] === 'Mendekati'): ?>
                            <strong class="text-warning">JATUH TEMPO (<?= $exp['days']; ?> hari)</strong>
                        <?php else: ?>
                            <strong class="text-success">BERLAKU (<?= $exp['days']; ?> hari)</strong>
                        <?php endif; ?>
                    </td>
                    <td class="text-center fw-bold <?= ($row['hasil'] === 'Laik Pakai') ? 'text-success' : 'text-danger'; ?>">
                        <?= htmlspecialchars($row['hasil']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="row mt-5 text-center small">
    <div class="col-6">
        <p class="mb-5">Pengawas Kalibrasi &amp; Kelaikan Medis,</p>
        <p class="mb-0 fw-bold"><u>Ahmad Teknisi Elektromedis</u></p>
        <span class="text-muted">Staf Penjamin Mutu Alat Medis</span>
    </div>
    <div class="col-6">
        <p class="mb-5">Mengetahui,<br>Kepala Bagian Sarana &amp; Prasarana,</p>
        <p class="mb-0 fw-bold"><u>Ir. H. Gunawan, M.T.</u></p>
        <span class="text-muted">NIP: 19820514 200801 1 003</span>
    </div>
</div>

</body>
</html>
