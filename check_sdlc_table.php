<?php
/**
 * Script untuk mengecek dan membuat tabel ticket_sdlc_data jika belum ada
 * Jalankan file ini sekali untuk memastikan tabel sudah dibuat
 */

require_once 'config.php';

try {
    $db = getDB();
    
    // Check if table exists
    $check_table = $db->query("SHOW TABLES LIKE 'ticket_sdlc_data'");
    $table_exists = $check_table->rowCount() > 0;
    
    if ($table_exists) {
        echo "✓ Table ticket_sdlc_data sudah ada.\n";
        
        // Check structure
        $columns = $db->query("SHOW COLUMNS FROM ticket_sdlc_data")->fetchAll();
        echo "✓ Table memiliki " . count($columns) . " kolom.\n";
        
        // Check if there's any data
        $count = $db->query("SELECT COUNT(*) as cnt FROM ticket_sdlc_data")->fetch();
        echo "✓ Jumlah data SDLC: " . $count['cnt'] . "\n";
        
    } else {
        echo "✗ Table ticket_sdlc_data belum ada. Membuat tabel...\n";
        
        // Create table
        $sql = "CREATE TABLE IF NOT EXISTS ticket_sdlc_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT NOT NULL,
            ticket_number VARCHAR(50) NOT NULL,
            judul_permintaan VARCHAR(500) NOT NULL,
            unit_departemen VARCHAR(255) NOT NULL,
            nama_pemohon VARCHAR(255) NOT NULL,
            jabatan VARCHAR(255) NOT NULL,
            email_pemohon VARCHAR(255) NOT NULL,
            tanggal_pengajuan DATE NOT NULL,
            prioritas_diusulkan ENUM('Tinggi', 'Sedang', 'Rendah') NOT NULL,
            latar_belakang TEXT NOT NULL,
            pernyataan_masalah TEXT NOT NULL,
            dampak_waktu TEXT NOT NULL,
            dampak_transparansi TEXT NOT NULL,
            dampak_biaya TEXT NOT NULL,
            dampak_akuntabilitas TEXT NOT NULL,
            tujuan_utama TEXT NOT NULL,
            manfaat_1 VARCHAR(500) NOT NULL,
            manfaat_2 VARCHAR(500) NOT NULL,
            manfaat_3 VARCHAR(500) NULL,
            pengguna_utama_sistem TEXT NOT NULL,
            fitur_kunci TEXT NOT NULL,
            pemohon_ttd VARCHAR(255) NOT NULL,
            manajer_ttd VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
            INDEX idx_ticket_id (ticket_id),
            INDEX idx_ticket_number (ticket_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->exec($sql);
        echo "✓ Table ticket_sdlc_data berhasil dibuat!\n";
    }
    
    // Check category enum
    $category_check = $db->query("SHOW COLUMNS FROM tickets WHERE Field = 'category'")->fetch();
    $enum_values = $category_check['Type'];
    
    if (strpos($enum_values, 'Permintaan pembuatan/perubahan aplikasi sistem') !== false) {
        echo "✓ Category 'Permintaan pembuatan/perubahan aplikasi sistem' sudah ada di ENUM.\n";
    } else {
        echo "✗ Category 'Permintaan pembuatan/perubahan aplikasi sistem' belum ada di ENUM.\n";
        echo "  Menambahkan category...\n";
        try {
            $db->exec("ALTER TABLE tickets MODIFY COLUMN category ENUM('Network', 'Software', 'Hardware', 'System & Aplikasi', 'Permintaan pembuatan/perubahan aplikasi sistem') NOT NULL");
            echo "✓ Category berhasil ditambahkan!\n";
        } catch (Exception $e) {
            echo "✗ Error menambahkan category: " . $e->getMessage() . "\n";
            echo "  Jalankan manual: ALTER TABLE tickets MODIFY COLUMN category ENUM('Network', 'Software', 'Hardware', 'System & Aplikasi', 'Permintaan pembuatan/perubahan aplikasi sistem') NOT NULL;\n";
        }
    }
    
    // Check for tickets with SDLC category but no SDLC data
    echo "\n--- Checking for tickets with SDLC category but no SDLC data ---\n";
    $sdlc_tickets = $db->query("SELECT id, ticket_number, category FROM tickets WHERE category = 'Permintaan pembuatan/perubahan aplikasi sistem'")->fetchAll();
    echo "Found " . count($sdlc_tickets) . " SDLC tickets.\n";
    
    foreach ($sdlc_tickets as $ticket) {
        $check = $db->prepare("SELECT id FROM ticket_sdlc_data WHERE ticket_id = ?");
        $check->execute([$ticket['id']]);
        if (!$check->fetch()) {
            echo "⚠ Ticket {$ticket['ticket_number']} (ID: {$ticket['id']}) tidak memiliki data SDLC\n";
        }
    }
    
    echo "\n✓ Pengecekan selesai!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>

