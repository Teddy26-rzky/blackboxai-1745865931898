<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil histori booking user
$stmt = $pdo->prepare('
    SELECT b.*, r.name AS room_name, r.price
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
');
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Histori Booking - Bale's Room</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Histori Booking</h1>
            <nav>
                <a href="rooms.php" class="text-blue-600 hover:underline mr-4">Daftar Kamar</a>
                <a href="logout.php" class="text-red-600 hover:underline">Keluar</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-10">
        <?php if (count($bookings) === 0): ?>
            <p class="text-center text-gray-600">Anda belum memiliki histori booking.</p>
        <?php else: ?>
            <table class="min-w-full bg-white rounded shadow overflow-hidden">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="py-3 px-4 text-left">Kamar</th>
                        <th class="py-3 px-4 text-left">Tanggal Booking</th>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-left">Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr class="border-t">
                            <td class="py-3 px-4"><?=htmlspecialchars($booking['room_name'])?></td>
                            <td class="py-3 px-4"><?=htmlspecialchars($booking['booking_date'])?></td>
                            <td class="py-3 px-4 capitalize"><?=htmlspecialchars($booking['status'])?></td>
                            <td class="py-3 px-4">
                                <?php if ($booking['invoice_url']): ?>
                                    <a href="<?=htmlspecialchars($booking['invoice_url'])?>" target="_blank" class="text-blue-600 hover:underline">Lihat Invoice</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>
