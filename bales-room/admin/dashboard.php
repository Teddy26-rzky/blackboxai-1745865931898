<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Ambil statistik pemesanan
$stmt = $pdo->query('SELECT status, COUNT(*) AS count FROM bookings GROUP BY status');
$stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Total booking
$total_booking = array_sum($stats);

// Siapkan data untuk tabel booking
$stmt = $pdo->query('
    SELECT b.id, u.name AS user_name, r.name AS room_name, b.booking_date, b.status
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    ORDER BY b.booking_date DESC
');
$bookings = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard Admin - Bale's Room</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard Admin</h1>
            <nav>
                <span class="mr-4">Halo, <?=htmlspecialchars($_SESSION['user_name'])?></span>
                <a href="../room_manage.php" class="text-blue-600 hover:underline mr-4">Kelola Kamar</a>
                <a href="../logout.php" class="text-red-600 hover:underline">Keluar</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-10">
        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Statistik Pemesanan</h2>
            <div class="grid grid-cols-3 gap-6 max-w-md">
                <div class="bg-white p-4 rounded shadow text-center">
                    <div class="text-3xl font-bold"><?= $total_booking ?></div>
                    <div>Total Booking</div>
                </div>
                <div class="bg-white p-4 rounded shadow text-center">
                    <div class="text-3xl font-bold"><?= $stats['pending'] ?? 0 ?></div>
                    <div>Pending</div>
                </div>
                <div class="bg-white p-4 rounded shadow text-center">
                    <div class="text-3xl font-bold"><?= $stats['confirmed'] ?? 0 ?></div>
                    <div>Confirmed</div>
                </div>
                <div class="bg-white p-4 rounded shadow text-center">
                    <div class="text-3xl font-bold"><?= $stats['cancelled'] ?? 0 ?></div>
                    <div>Cancelled</div>
                </div>
            </div>
        </section>
        <section>
            <h2 class="text-xl font-semibold mb-4">Daftar Booking</h2>
            <table class="min-w-full bg-white rounded shadow overflow-hidden">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="py-3 px-4 text-left">ID</th>
                        <th class="py-3 px-4 text-left">User</th>
                        <th class="py-3 px-4 text-left">Kamar</th>
                        <th class="py-3 px-4 text-left">Tanggal Booking</th>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-left">Metode Bayar</th>
                        <th class="py-3 px-4 text-left">Status Bayar</th>
                        <th class="py-3 px-4 text-left">Invoice</th>
                        <th class="py-3 px-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr class="border-t">
                            <td class="py-3 px-4"><?=htmlspecialchars($booking['id'])?></td>
                            <td class="py-3 px-4"><?=htmlspecialchars($booking['user_name'])?></td>
                            <td class="py-3 px-4"><?=htmlspecialchars($booking['room_name'])?></td>
                            <td class="py-3 px-4"><?=htmlspecialchars($booking['booking_date'])?></td>
                            <td class="py-3 px-4 capitalize"><?=htmlspecialchars($booking['status'])?></td>
                            <td class="py-3 px-4"><?=htmlspecialchars($booking['payment_method'] ?? '-')?></td>
                            <td class="py-3 px-4 capitalize"><?=htmlspecialchars($booking['payment_status'] ?? '-')?></td>
                            <td class="py-3 px-4">
                                <?php if (!empty($booking['invoice_url'])): ?>
                                    <a href="<?=htmlspecialchars($booking['invoice_url'])?>" target="_blank" class="text-blue-600 hover:underline">Lihat Invoice</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4">
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <form method="POST" action="confirm_booking.php" class="inline">
                                        <input type="hidden" name="booking_id" value="<?=htmlspecialchars($booking['id'])?>" />
                                        <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition">Konfirmasi</button>
                                    </form>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <section class="mt-8">
            <form method="POST" action="export_excel.php">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Ekspor Statistik ke Excel</button>
            </form>
        </section>
    </main>
</body>
</html>
