<?php
$pwd = "demo123";
$hashed = '$2y$10$f5UZAEEm01/N2mPFD.aKP.0TxxYNyYgUE9xkZ2Ogjb.dhJKWa0o/y';
if (password_verify($pwd, $hashed)) {
    echo "Password benar!";
} else {
    echo "Password SALAH!";
}
?>