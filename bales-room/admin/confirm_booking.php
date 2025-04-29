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
                // Update status booking menjadi confirmed dan payment_status menjadi paid
                $stmt = $pdo->prepare('UPDATE bookings SET status = "confirmed", payment_status = "paid" WHERE id = ?');
                $stmt->execute([$booking_id]);

                // Generate invoice HTML file
                $invoice_dir = __DIR__ . '/../invoices/';
                if (!is_dir($invoice_dir)) {
                    mkdir($invoice_dir, 0755, true);
                }
                $invoice_filename = 'invoice_' . $booking_id . '.html';
                $invoice_path = $invoice_dir . $invoice_filename;

                $invoice_content = "<!DOCTYPE html>
<html lang='id'>
<head>
<meta charset='UTF-8'>
<title>Invoice Pemesanan #$booking_id</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #333; }
table { border-collapse: collapse; width: 100%; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
</style>
</head>
<body>
<h1>Invoice Pemesanan #$booking_id</h1>
<p>Nama User: " . htmlspecialchars($booking['user_id']) . "</p>
<p>Tanggal Booking: " . htmlspecialchars($booking['booking_date']) . "</p>
<p>Status Booking: " . htmlspecialchars($booking['status']) . "</p>
<p>Metode Pembayaran: " . htmlspecialchars($booking['payment_method']) . "</p>
<p>Status Pembayaran: " . htmlspecialchars($booking['payment_status']) . "</p>
</body>
</html>";

                file_put_contents($invoice_path, $invoice_content);

                // Update invoice_url di database
                $stmt = $pdo->prepare('UPDATE bookings SET invoice_url = ? WHERE id = ?');
                $stmt->execute(['invoices/' . $invoice_filename, $booking_id]);

                $_SESSION['success'] = 'Booking berhasil dikonfirmasi dan invoice telah dibuat.';
            }
        }
    }
}

header('Location: dashboard.php');
exit;
