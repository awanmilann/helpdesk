<?php
require_once 'config.php';
try {
    $db = getDB();
    $result = $db->query("SELECT * FROM users");
    foreach ($result as $row) {
        echo $row['username'] . ' - ' . $row['email'] . '<br>';
    }
    echo "Koneksi database dan fetch user BERHASIL!";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>