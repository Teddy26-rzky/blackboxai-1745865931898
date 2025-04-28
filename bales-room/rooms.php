<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: login.php');
    exit;
}

// Ambil daftar kamar yang statusnya available
$stmt = $pdo->prepare('SELECT * FROM rooms WHERE status = "available"');
$stmt->execute();
$rooms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Daftar Kamar - Bale's Room</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Bale's Room - Daftar Kamar</h1>
            <nav>
                <span class="mr-4">Halo, <?=htmlspecialchars($_SESSION['user_name'])?></span>
                <a href="booking_history.php" class="text-blue-600 hover:underline mr-4">Histori Booking</a>
                <a href="logout.php" class="text-red-600 hover:underline">Keluar</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-10">
        <?php if (count($rooms) === 0): ?>
            <p class="text-center text-gray-600">Tidak ada kamar tersedia saat ini.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($rooms as $room): ?>
                    <div class="bg-white rounded shadow p-4 flex flex-col">
                        <?php if ($room['photo']): ?>
                            <img src="uploads/<?=htmlspecialchars($room['photo'])?>" alt="<?=htmlspecialchars($room['name'])?>" class="mb-4 rounded h-48 object-cover" />
                        <?php else: ?>
                            <div class="mb-4 rounded h-48 bg-gray-200 flex items-center justify-center text-gray-400">Tidak ada foto</div>
                        <?php endif; ?>
                        <h2 class="text-xl font-semibold mb-2"><?=htmlspecialchars($room['name'])?></h2>
                        <p class="text-gray-700 mb-2"><?=htmlspecialchars($room['description'])?></p>
                        <p class="font-bold mb-4">Harga: Rp <?=number_format($room['price'], 0, ',', '.')?></p>
                        <a href="booking.php?room_id=<?=htmlspecialchars($room['id'])?>" class="mt-auto bg-blue-600 text-white py-2 rounded hover:bg-blue-700 text-center transition">Booking</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
