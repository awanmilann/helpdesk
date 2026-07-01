<?php
require_once 'config.php';

try {
    $db = getDB();
    
    // Cek tabel users
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    
    echo "<h3>Struktur Tabel Users:</h3>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    // Cek data users
    $stmt = $db->query("SELECT * FROM users");
    $users = $stmt->fetchAll();
    
    echo "<h3>Data Users:</h3>";
    echo "<pre>";
    print_r($users);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>