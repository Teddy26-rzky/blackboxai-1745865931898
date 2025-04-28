<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = false;

// Handle tambah atau edit kamar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $status = $_POST['status'] ?? 'available';

    if (!$name) {
        $errors[] = 'Nama kamar harus diisi.';
    }
    if (!is_numeric($price) || $price <= 0) {
        $errors[] = 'Harga harus berupa angka positif.';
    }

    // Handle upload foto
    $photo_filename = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $tmp_name = $_FILES['photo']['tmp_name'];
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_filename = uniqid() . '.' . $ext;
        move_uploaded_file($tmp_name, $upload_dir . $photo_filename);
    }

    if (empty($errors)) {
        if ($id) {
            // Update kamar
            if ($photo_filename) {
                $stmt = $pdo->prepare('UPDATE rooms SET name = ?, description = ?, price = ?, status = ?, photo = ? WHERE id = ?');
                $stmt->execute([$name, $description, $price, $status, $photo_filename, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE rooms SET name = ?, description = ?, price = ?, status = ? WHERE id = ?');
                $stmt->execute([$name, $description, $price, $status, $id]);
            }
            $success = 'Kamar berhasil diperbarui.';
        } else {
            // Insert kamar baru
            $stmt = $pdo->prepare('INSERT INTO rooms (name, description, price, status, photo) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $description, $price, $status, $photo_filename]);
            $success = 'Kamar berhasil ditambahkan.';
        }
    }
}

// Ambil daftar kamar
$stmt = $pdo->query('SELECT * FROM rooms ORDER BY created_at DESC');
$rooms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kelola Kamar - Bale's Room</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Kelola Kamar</h1>
            <nav>
                <a href="admin/dashboard.php" class="text-blue-600 hover:underline mr-4">Dashboard</a>
                <a href="logout.php" class="text-red-600 hover:underline">Keluar</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-10 max-w-4xl">
        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded mb-6"><?=htmlspecialchars($success)?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?=htmlspecialchars($error)?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Tambah / Edit Kamar</h2>
            <form method="POST" action="room_manage.php" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="id" id="room_id" />
                <label class="block mb-2 font-semibold" for="name">Nama Kamar</label>
                <input type="text" id="name" name="name" required class="w-full mb-4 px-3 py-2 border rounded" />
                <label class="block mb-2 font-semibold" for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="3" class="w-full mb-4 px-3 py-2 border rounded"></textarea>
                <label class="block mb-2 font-semibold" for="price">Harga (Rp)</label>
                <input type="number" id="price" name="price" required class="w-full mb-4 px-3 py-2 border rounded" />
                <label class="block mb-2 font-semibold" for="status">Status</label>
                <select id="status" name="status" class="w-full mb-4 px-3 py-2 border rounded">
                    <option value="available">Tersedia</option>
                    <option value="maintenance">Maintenance</option>
                </select>
                <label class="block mb-2 font-semibold" for="photo">Foto Kamar</label>
                <input type="file" id="photo" name="photo" accept="image/*" class="mb-6" />
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Simpan</button>
            </form>
        </section>
        <section>
            <h2 class="text-xl font-semibold mb-4">Daftar Kamar</h2>
            <?php if (count($rooms) === 0): ?>
                <p class="text-gray-600">Belum ada kamar yang ditambahkan.</p>
            <?php else: ?>
                <table class="min-w-full bg-white rounded shadow overflow-hidden">
                    <thead class="bg-gray-200 text-gray-700">
                        <tr>
                            <th class="py-3 px-4 text-left">Foto</th>
                            <th class="py-3 px-4 text-left">Nama</th>
                            <th class="py-3 px-4 text-left">Deskripsi</th>
                            <th class="py-3 px-4 text-left">Harga</th>
                            <th class="py-3 px-4 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                            <tr class="border-t">
                                <td class="py-3 px-4">
                                    <?php if ($room['photo']): ?>
                                        <img src="uploads/<?=htmlspecialchars($room['photo'])?>" alt="<?=htmlspecialchars($room['name'])?>" class="h-16 w-24 object-cover rounded" />
                                    <?php else: ?>
                                        <span class="text-gray-400">Tidak ada foto</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4"><?=htmlspecialchars($room['name'])?></td>
                                <td class="py-3 px-4"><?=htmlspecialchars($room['description'])?></td>
                                <td class="py-3 px-4">Rp <?=number_format($room['price'], 0, ',', '.')?></td>
                                <td class="py-3 px-4 capitalize"><?=htmlspecialchars($room['status'])?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
    <script>
        // Script untuk mengisi form edit kamar jika ingin dikembangkan
    </script>
</body>
</html>
