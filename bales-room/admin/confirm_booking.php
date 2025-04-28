<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? null;
    if ($booking_id) {
        // Ambil booking
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ?');
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch();

        if ($booking && $booking['status'] === 'pending') {
            // Cek apakah kamar masih tersedia pada tanggal booking
            $stmt = $pdo->prepare('SELECT * FROM bookings WHERE room_id = ? AND booking_date = ? AND status = "confirmed"');
            $stmt->execute([$booking['room_id'], $booking['booking_date']]);
            if ($stmt->fetch()) {
                // Sudah ada booking confirmed, batal konfirmasi
                $_SESSION['error'] = 'Booking gagal dikonfirmasi karena kamar sudah dibooking user lain pada tanggal tersebut.';
            } else {
                // Update status booking menjadi confirmed
                $stmt = $pdo->prepare('UPDATE bookings SET status = "confirmed" WHERE id = ?');
                $stmt->execute([$booking_id]);
                $_SESSION['success'] = 'Booking berhasil dikonfirmasi.';
            }
        }
    }
}

header('Location: dashboard.php');
exit;
