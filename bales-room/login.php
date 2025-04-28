<?php
session_start();
require 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid.';
    }
    if (!$password) {
        $errors[] = 'Password harus diisi.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, name, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: rooms.php');
            }
            exit;
        } else {
            $errors[] = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Masuk - Bale's Room</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="max-w-md mx-auto mt-20 bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-6 text-center">Masuk ke Akun Anda</h1>
        <?php if ($errors): ?>
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?=htmlspecialchars($error)?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="POST" action="login.php" novalidate>
            <label class="block mb-2 font-semibold" for="email">Email</label>
            <input type="email" id="email" name="email" required class="w-full mb-4 px-3 py-2 border rounded" value="<?=htmlspecialchars($_POST['email'] ?? '')?>" />
            <label class="block mb-2 font-semibold" for="password">Password</label>
            <input type="password" id="password" name="password" required class="w-full mb-6 px-3 py-2 border rounded" />
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded hover:bg-blue-700 transition">Masuk</button>
        </form>
        <p class="mt-4 text-center text-gray-600">Belum punya akun? <a href="register.php" class="text-blue-600 hover:underline">Daftar di sini</a></p>
    </div>
</body>
</html>
