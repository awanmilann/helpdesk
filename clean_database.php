<?php
require_once 'config.php';

// Only allow logged-in admin to clean database
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$db = getDB();

// Optional: delete uploaded files (except .gitkeep)
function deleteUploadedFiles($dir = 'uploads') {
    if (!is_dir($dir)) return;
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === '.gitkeep') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_file($path)) @unlink($path);
    }
}

try {
    // Begin transaction
    $db->beginTransaction();

    // Clean attachments and tickets
    $db->exec("DELETE FROM attachments");
    $db->exec("DELETE FROM tickets");

    // Reset ticket counter to 0 or 1
    // If table exists, set to 0; if not, ignore
    try {
        $db->exec("UPDATE ticket_counter SET counter = 0 WHERE id = 1");
    } catch (Exception $e) {}

    $db->commit();

    // Delete uploaded files on disk
    deleteUploadedFiles('uploads');

    echo 'OK';
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo 'ERROR: ' . $e->getMessage();
}
?>


