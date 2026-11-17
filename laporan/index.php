<?php
/**
 * Pusat Laporan & Cetak Rekapitulasi
 */
$page_title = 'Pusat Laporan & Dokumen';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Pusat Cetak &amp; Laporan Eksekutif</h4>
        <p class="text-muted small mb-0">Cetak dokumen resmi, berita acara, dan rekapitulasi audit akreditasi rumah sakit.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Laporan 1: Master Inventaris Alat -->
    <div class="col-md-4">
        <div class="card-custom h-100 d-flex flex-column justify-content-between">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                        <i class="fas fa-boxes-stacked fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Laporan Inventaris Alat</h6>
                        <small class="text-muted">Master data seluruh aset</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-4">
                    Mencetak seluruh daftar alat medis lengkap dengan lokasi ruangan, merek, nomor seri pabrik, kondisi fisik, dan nilai perolehan aset.
                </p>
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL; ?>/laporan/inventaris.php" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-print me-1"></i> Cetak Laporan Inventaris
                    </a>
                    <a href="<?= BASE_URL; ?>/inventaris/export.php" class="btn btn-light border text-secondary">
                        <i class="fas fa-file-excel text-success me-1"></i> Unduh Format CSV/Excel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan 2: Rekap Pemeliharaan / Servis -->
    <div class="col-md-4">
        <div class="card-custom h-100 d-flex flex-column justify-content-between">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-2">
                        <i class="fas fa-screwdriver-wrench fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Laporan Pemeliharaan</h6>
                        <small class="text-muted">Log servis preventif &amp; perbaikan</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Rekapitulasi tindakan pemeliharaan alat, teknisi penanggung jawab, penggantian suku cadang, dan rincian biaya yang dikeluarkan.
                </p>
                <form action="<?= BASE_URL; ?>/laporan/pemeliharaan.php" method="GET" target="_blank" class="mb-3">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="small text-muted">Dari Tgl</label>
                            <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?= date('Y-m-01'); ?>">
                        </div>
                        <div class="col-6">
                            <label class="small text-muted">Sampai Tgl</label>
                            <input type="date" name="tgl_selesai" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-warning text-dark fw-bold w-100">
                        <i class="fas fa-print me-1"></i> Cetak Rekap Pemeliharaan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Laporan 3: Rekap Kalibrasi & Kelaikan -->
    <div class="col-md-4">
        <div class="card-custom h-100 d-flex flex-column justify-content-between">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-2">
                        <i class="fas fa-award fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Laporan Kalibrasi</h6>
                        <small class="text-muted">Sertifikasi &amp; uji kelaikan</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Rekapitulasi masa berlaku sertifikasi kalibrasi untuk pemenuhan standar audit akreditasi fasilitas kesehatan KARS / Kemenkes.
                </p>
                <form action="<?= BASE_URL; ?>/laporan/kalibrasi.php" method="GET" target="_blank">
                    <div class="mb-2">
                        <label class="small text-muted">Status Masa Berlaku</label>
                        <select name="status_filter" class="form-select form-select-sm">
                            <option value="">Semua Status Sertifikat</option>
                            <option value="kadaluarsa">Hanya Yang Sudah Kadaluarsa</option>
                            <option value="mendekati">Jatuh Tempo dlm 30 Hari</option>
                            <option value="berlaku">Masih Berlaku</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-info text-dark fw-bold w-100">
                        <i class="fas fa-print me-1"></i> Cetak Rekap Kalibrasi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
