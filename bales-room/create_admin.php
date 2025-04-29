<?php
require 'config.php';

$name = 'Admin Bale';
$email = 'admin@balesroom.com';
$password = 'admin123'; // password plain text as requested
$role = 'admin';

// Cek apakah admin sudah ada
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo "Admin sudah ada.\n";
    exit;
}

// Insert admin dengan password plain text (tidak di-hash)
$stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
$stmt->execute([$name, $email, $password, $role]);

echo "Admin berhasil dibuat dengan email: $email dan password: $password\n";
?>
