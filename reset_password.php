<?php
require_once 'config.php';

try {
    $db = getDB();
    
    $new_password = 'password';
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hashed_password, 'admin@helpdesk.local']);
    
    echo "Password reset successfully to: " . $new_password . "<br>";
    echo "Hashed: " . $hashed_password . "<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>