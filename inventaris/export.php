<?php
/**
 * Export Inventaris Alat ke CSV
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helper.php';

require_login();

$filename = "Laporan_Inventaris_Alat_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Tambah UTF-8 BOM agar terbaca rapi di Microsoft Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header Kolom
fputcsv($output, [
    'No',
    'Kode Alat',
    'Nama Alat',
    'Kategori',
    'Merek',
    'Tipe / Model',
    'Nomor Seri',
    'Lokasi Ruangan',
    'Tahun Pengadaan',
    'Harga Pengadaan (Rp)',
    'Kondisi Fisik',
    'Status Operasional',
    'Spesifikasi / Catatan'
]);

$stmt = $pdo->query("SELECT * FROM alat ORDER BY id ASC");
$no = 1;
while ($row = $stmt->fetch()) {
    fputcsv($output, [
        $no++,
        $row['kode_alat'],
        $row['nama_alat'],
        $row['kategori'],
        $row['merek'],
        $row['tipe_model'] ?: '-',
        $row['nomor_seri'] ?: '-',
        $row['lokasi'],
        $row['tahun'],
        (int)$row['harga_pengadaan'],
        $row['kondisi'],
        $row['status_operasional'],
        str_replace(["\r", "\n"], ' ', $row['spesifikasi'] ?? '')
    ]);
}

fclose($output);
exit;
