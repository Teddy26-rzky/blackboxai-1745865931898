<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$room_id = $_GET['room_id'] ?? null;
if (!$room_id) {
    header('Location: rooms.php');
    exit;
}

// Ambil data kamar
$stmt = $pdo->prepare('SELECT * FROM rooms WHERE id = ? AND status = "available"');
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    die('Kamar tidak ditemukan atau tidak tersedia.');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = $_POST['booking_date'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';

    if (!$booking_date) {
        $errors[] = 'Tanggal booking harus diisi.';
    }
    if (!$payment_method || !in_array($payment_method, ['transfer', 'cash'])) {
        $errors[] = 'Metode pembayaran harus dipilih.';
    } else {
        // Cek apakah sudah ada booking untuk kamar dan tanggal tersebut dengan status pending atau confirmed
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE room_id = ? AND booking_date = ? AND status IN ("pending", "confirmed")');
        $stmt->execute([$room_id, $booking_date]);
        if ($stmt->fetch()) {
            $errors[] = 'Maaf, kamar sudah dibooking pada tanggal tersebut.';
        }
    }

    if (empty($errors)) {
        // Simpan booking dengan status pending dan metode pembayaran
        $stmt = $pdo->prepare('INSERT INTO bookings (user_id, room_id, booking_date, status, payment_method, payment_status) VALUES (?, ?, ?, "pending", ?, "unpaid")');
        $stmt->execute([$_SESSION['user_id'], $room_id, $booking_date, $payment_method]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Booking Kamar - Bale's Room</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Booking Kamar: <?=htmlspecialchars($room['name'])?></h1>
            <nav>
                <a href="rooms.php" class="text-blue-600 hover:underline mr-4">Kembali ke Daftar Kamar</a>
                <a href="logout.php" class="text-red-600 hover:underline">Keluar</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-10 max-w-md">
        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded mb-6">
                Booking berhasil dibuat dan menunggu konfirmasi admin.
            </div>
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
<form method="POST" action="booking.php?room_id=<?=htmlspecialchars($room_id)?>" novalidate>
    <label for="booking_date" class="block mb-2 font-semibold">Tanggal Booking</label>
    <input type="date" id="booking_date" name="booking_date" required class="w-full mb-4 px-3 py-2 border rounded" />
    <label for="payment_method" class="block mb-2 font-semibold">Metode Pembayaran</label>
    <select id="payment_method" name="payment_method" required class="w-full mb-6 px-3 py-2 border rounded">
        <option value="">-- Pilih Metode Pembayaran --</option>
        <option value="transfer">Transfer</option>
        <option value="cash">Cash</option>
    </select>
    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded hover:bg-blue-700 transition">Booking Sekarang</button>
</form>
    </main>
</body>
</html>
