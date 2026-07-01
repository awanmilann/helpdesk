-- Update database untuk menambahkan category baru dan tabel SDLC
-- Jalankan file ini di phpMyAdmin atau MySQL client

USE u335370311_ticket;

-- 1. Tambahkan category baru ke ENUM di tabel tickets
ALTER TABLE tickets 
MODIFY COLUMN category ENUM(
    'Network', 
    'Software', 
    'Hardware', 
    'System & Aplikasi',
    'Pengajuan pembuatan atau perubahan aplikasi sistem'
) NOT NULL;

-- 2. Buat tabel untuk menyimpan data form SDLC
CREATE TABLE IF NOT EXISTS ticket_sdlc_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    ticket_number VARCHAR(50) NOT NULL,
    
    -- Bagian A: Informasi Umum
    judul_permintaan VARCHAR(500) NOT NULL,
    unit_departemen VARCHAR(255) NOT NULL,
    nama_pemohon VARCHAR(255) NOT NULL,
    jabatan VARCHAR(255) NOT NULL,
    email_pemohon VARCHAR(255) NOT NULL,
    tanggal_pengajuan DATE NOT NULL,
    prioritas_diusulkan ENUM('Tinggi', 'Sedang', 'Rendah') NOT NULL,
    
    -- Bagian B: Deskripsi Kebutuhan
    latar_belakang TEXT NOT NULL,
    pernyataan_masalah TEXT NOT NULL,
    dampak_waktu TEXT NOT NULL,
    dampak_transparansi TEXT NOT NULL,
    dampak_biaya TEXT NOT NULL,
    dampak_akuntabilitas TEXT NOT NULL,
    
    -- Bagian C: Solusi yang Diusulkan
    tujuan_utama TEXT NOT NULL,
    manfaat_1 VARCHAR(500) NOT NULL,
    manfaat_2 VARCHAR(500) NOT NULL,
    manfaat_3 VARCHAR(500) NULL,
    pengguna_utama_sistem TEXT NOT NULL, -- JSON format untuk array user roles
    fitur_kunci TEXT NOT NULL, -- JSON format untuk array features
    
    -- Persetujuan
    pemohon_ttd VARCHAR(255) NOT NULL,
    manajer_ttd VARCHAR(255) NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    INDEX idx_ticket_id (ticket_id),
    INDEX idx_ticket_number (ticket_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

