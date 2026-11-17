-- Database Schema & Seed Data for SIMRS / Sistem Manajemen Inventaris, Pemeliharaan & Kalibrasi
CREATE DATABASE IF NOT EXISTS `simrs_crud` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `simrs_crud`;

-- 1. Tabel Users (Autentikasi Pengguna)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'teknisi', 'operator') NOT NULL DEFAULT 'admin',
  `foto` VARCHAR(255) DEFAULT 'default.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Alat (Inventaris Fasilitas & Elektromedis)
CREATE TABLE IF NOT EXISTS `alat` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_alat` VARCHAR(50) NOT NULL UNIQUE,
  `nama_alat` VARCHAR(150) NOT NULL,
  `kategori` VARCHAR(50) NOT NULL,
  `merek` VARCHAR(100) NOT NULL,
  `tipe_model` VARCHAR(100) DEFAULT NULL,
  `nomor_seri` VARCHAR(100) DEFAULT NULL,
  `lokasi` VARCHAR(100) NOT NULL,
  `tahun` INT NOT NULL,
  `harga_pengadaan` DECIMAL(15,2) DEFAULT 0,
  `kondisi` ENUM('Baik', 'Rusak Ringan', 'Rusak Berat') NOT NULL DEFAULT 'Baik',
  `status_operasional` ENUM('Siap Pakai', 'Dalam Pemeliharaan', 'Dalam Kalibrasi', 'Rusak', 'Afkir') NOT NULL DEFAULT 'Siap Pakai',
  `foto` VARCHAR(255) DEFAULT NULL,
  `spesifikasi` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Pemeliharaan (Maintenance Rutin & Korektif)
CREATE TABLE IF NOT EXISTS `pemeliharaan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_pemeliharaan` VARCHAR(50) NOT NULL UNIQUE,
  `id_alat` INT NOT NULL,
  `jenis_pemeliharaan` ENUM('Preventif', 'Korektif', 'Inspeksi Rutin', 'Darurat') NOT NULL DEFAULT 'Preventif',
  `tanggal_jadwal` DATE NOT NULL,
  `tanggal_selesai` DATE DEFAULT NULL,
  `teknisi` VARCHAR(100) NOT NULL,
  `keluhan` TEXT DEFAULT NULL,
  `tindakan` TEXT DEFAULT NULL,
  `suku_cadang` VARCHAR(255) DEFAULT NULL,
  `biaya` DECIMAL(15,2) DEFAULT 0,
  `status` ENUM('Terjadwal', 'Sedang Dikerjakan', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Terjadwal',
  `catatan` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pemeliharaan_alat` FOREIGN KEY (`id_alat`) REFERENCES `alat` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Kalibrasi & Uji Laik Pakai
CREATE TABLE IF NOT EXISTS `kalibrasi` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_alat` INT NOT NULL,
  `nomor_sertifikat` VARCHAR(100) NOT NULL,
  `lembaga_kalibrasi` VARCHAR(150) NOT NULL,
  `tanggal_kalibrasi` DATE NOT NULL,
  `tanggal_kadaluarsa` DATE NOT NULL,
  `hasil` ENUM('Laik Pakai', 'Tidak Laik Pakai') NOT NULL DEFAULT 'Laik Pakai',
  `biaya` DECIMAL(15,2) DEFAULT 0,
  `dokumen` VARCHAR(255) DEFAULT NULL,
  `catatan` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_kalibrasi_alat` FOREIGN KEY (`id_alat`) REFERENCES `alat` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Awal Pengguna (Password default: 'admin123' di-hash dengan BCRYPT)
-- Hash untuk 'admin123' adalah: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- Kita juga sediakan akun teknisi 'teknisi' (password: admin123)
INSERT INTO `users` (`id`, `username`, `nama_lengkap`, `email`, `password`, `role`, `foto`) VALUES
(1, 'admin', 'Administrator Sistem', 'admin@medika.local', '$2y$10$H7gGbWU/otCLQG41tJs.8e.XjUlXehbRT31J2ttTXaznbDwluNLj2', 'admin', 'default.png'),
(2, 'teknisi', 'Ahmad Teknisi Elektromedis', 'teknisi@medika.local', '$2y$10$H7gGbWU/otCLQG41tJs.8e.XjUlXehbRT31J2ttTXaznbDwluNLj2', 'teknisi', 'default.png')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Sample Data Alat Inventaris
INSERT INTO `alat` (`id`, `kode_alat`, `nama_alat`, `kategori`, `merek`, `tipe_model`, `nomor_seri`, `lokasi`, `tahun`, `harga_pengadaan`, `kondisi`, `status_operasional`, `spesifikasi`) VALUES
(1, 'ALT-2023-001', 'Patient Monitor Multi Parameter', 'Elektromedis Life Support', 'Mindray', 'uMEC 10', 'SN-MND-992182', 'Ruang ICU Bed 1', 2023, 45000000.00, 'Baik', 'Siap Pakai', 'Monitor 10.4 inch, ECG 5-lead, SpO2, NIBP, Temp, Resp, Baterai Li-ion tahan 4 jam.'),
(2, 'ALT-2022-002', 'Defibrillator Biphasic Touch', 'Elektromedis Life Support', 'Philips', 'HeartStart XL+', 'SN-PHL-774011', 'Ruang IGD', 2022, 125000000.00, 'Baik', 'Siap Pakai', 'Energi 1-200 Joule, Layar warna 6.5 inch, Pacing eksternal, AED mode & manual.'),
(3, 'ALT-2021-003', 'Infusion Pump Digital', 'Elektromedis Terapi', 'Terumo', 'TE-171', 'SN-TRM-332910', 'Ruang Rawat Inap Melati', 2021, 18500000.00, 'Baik', 'Siap Pakai', 'Flow rate 0.1 - 1200 mL/h, Akurasi +-5%, Sensor udara optik ganda.'),
(4, 'ALT-2023-004', 'USG 4D Color Doppler', 'Elektromedis Diagnostik', 'GE Healthcare', 'Voluson E8', 'SN-GEH-552199', 'Poli Kebidanan & Kandungan', 2023, 420000000.00, 'Baik', 'Siap Pakai', 'Transduser Konveks, Transvaginal, Linear. Layar LED 23 inch, HD Live rendering.'),
(5, 'ALT-2020-005', 'Ventilator Transport & ICU', 'Elektromedis Life Support', 'Hamilton Medical', 'Hamilton C1', 'SN-HML-104921', 'Ruang ICU Bed 3', 2020, 280000000.00, 'Rusak Ringan', 'Dalam Pemeliharaan', 'Mode ASV, NIV, Baterai cadangan, perlu penggantian sensor O2 dan flow sensor.'),
(6, 'ALT-2022-006', 'Electrocardiograph (ECG 12-Channel)', 'Elektromedis Diagnostik', 'Fukuda Denshi', 'Cardimax FX-8222', 'SN-FKD-448102', 'Poli Jantung', 2022, 38000000.00, 'Baik', 'Siap Pakai', 'Analisis otomatis interpretasi, layar LCD 7 inch, thermal printer 210mm.'),
(7, 'ALT-2019-007', 'Mobile X-Ray Unit 100mA', 'Radiologi', 'Shimadzu', 'MobileArt Evolution', 'SN-SMZ-201988', 'Ruang Radiologi', 2019, 350000000.00, 'Baik', 'Siap Pakai', 'Kapasitas 100mA / 125kV, generator frekuensi tinggi, baterai motorized drive.'),
(8, 'ALT-2021-008', 'Autoclave Steam Sterilizer 50L', 'Laboratorium & Bedah', 'Tuttnauer', '3870E', 'SN-TTN-881230', 'Ruang CSSD (Sterilisasi)', 2021, 95000000.00, 'Baik', 'Siap Pakai', 'Kapasitas 50 Liter, Suhu 121-134 C, Pengeringan vakum otomatis, chamber stainless 316L.')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Sample Data Pemeliharaan
INSERT INTO `pemeliharaan` (`id`, `kode_pemeliharaan`, `id_alat`, `jenis_pemeliharaan`, `tanggal_jadwal`, `tanggal_selesai`, `teknisi`, `keluhan`, `tindakan`, `suku_cadang`, `biaya`, `status`, `catatan`) VALUES
(1, 'MNT-2026-001', 5, 'Korektif', '2026-11-10', NULL, 'Ahmad Teknisi', 'Alarm konsentrasi oksigen sering berbunyi, sensor O2 tidak stabil.', 'Pengecekan jalur gas, kalibrasi ulang internal, pemesanan sensor O2 pengganti.', 'Oxygen Sensor Hamilton C1', 3500000.00, 'Sedang Dikerjakan', 'Menunggu part tiba dari distributor resmi.'),
(2, 'MNT-2026-002', 1, 'Preventif', '2026-11-05', '2026-11-05', 'Ahmad Teknisi', 'Perawatan berkala 6 bulanan.', 'Pembersihan filter debu, uji kabel lead ECG, uji akurasi manset NIBP, pengujian baterai.', 'Filter debu NIBP', 450000.00, 'Selesai', 'Alat berfungsi optimal dan aman digunakan.'),
(3, 'MNT-2026-003', 3, 'Preventif', '2026-11-25', NULL, 'Rian Hidayat', 'Jadwal servis preventif triwulan IV.', 'Pengecekan flow rate akurasi dan pembersihan optik sensor.', NULL, 250000.00, 'Terjadwal', 'Pelaksanaan terjadwal minggu ke-4 November.'),
(4, 'MNT-2026-004', 2, 'Inspeksi Rutin', '2026-10-18', '2026-10-18', 'Ahmad Teknisi', 'Uji keandalan baterai & daya kejut.', 'Discharge test 50 Joule & 200 Joule ke dummy load, cek respon layar LCD.', 'Kertas thermal chart', 300000.00, 'Selesai', 'Baterai sehat, output energi sesuai toleransi standar IEC.')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Sample Data Kalibrasi
INSERT INTO `kalibrasi` (`id`, `id_alat`, `nomor_sertifikat`, `lembaga_kalibrasi`, `tanggal_kalibrasi`, `tanggal_kadaluarsa`, `hasil`, `biaya`, `catatan`) VALUES
(1, 1, 'SERT-BPFK-2026-0412', 'Balai Pengujian Fasilitas Kesehatan (BPFK)', '2026-01-15', '2027-01-15', 'Laik Pakai', 1800000.00, 'Semua parameter ECG, NIBP, SpO2 dalam batas toleransi aman.'),
(2, 2, 'SERT-KAL-2025-1102', 'PT. Medika Kalibrasi Mandiri', '2025-12-05', '2026-12-05', 'Laik Pakai', 2500000.00, 'Masa kalibrasi akan berakhir dalam waktu dekat. Perlu segera dijadwalkan kalibrasi ulang.'),
(3, 3, 'SERT-BPFK-2026-0881', 'Balai Pengujian Fasilitas Kesehatan (BPFK)', '2026-03-20', '2027-03-20', 'Laik Pakai', 1200000.00, 'Akurasi laju tetesan 99.1%, lolos uji keselamatan listrik IEC 60601.'),
(4, 6, 'SERT-DKI-2025-0911', 'Balai Kalibrasi Kemenkes RI', '2025-10-10', '2026-10-10', 'Laik Pakai', 1500000.00, 'Masa berlaku kalibrasi sudah kadaluarsa! Alat wajib dikalibrasi sebelum dioperasikan.')
ON DUPLICATE KEY UPDATE `id`=`id`;
