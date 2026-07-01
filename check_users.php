<?php
require_once 'config.php';

$db = getDB();

echo "<h2>Users in Database:</h2>";

$stmt = $db->query("SELECT id, name, email, department, password, role FROM users");
$users = $stmt->fetchAll();

if (count($users) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Department</th><th>Password</th><th>Role</th></tr>";
    
    foreach ($users as $row) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['department'] . "</td>";
        echo "<td>" . substr($row['password'], 0, 20) . "...</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No users found in database.";
}

echo "<h3>Test Login:</h3>";
echo "<form method='post' action='test_login.php'>";
echo "Email: <input type='email' name='email' value='admin@helpdesk.local'><br>";
echo "Password: <input type='password' name='password' value='demo123'><br>";
echo "<input type='submit' value='Test Login'>";
echo "</form>";

// PDO connection will be closed automatically
?>