<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Ambil data booking
$stmt = $pdo->query('
    SELECT b.id, u.name AS user_name, r.name AS room_name, b.booking_date, b.status
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    ORDER BY b.booking_date DESC
');
$bookings = $stmt->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data Booking');

$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'User');
$sheet->setCellValue('C1', 'Kamar');
$sheet->setCellValue('D1', 'Tanggal Booking');
$sheet->setCellValue('E1', 'Status');

$row = 2;
foreach ($bookings as $booking) {
    $sheet->setCellValue('A' . $row, $booking['id']);
    $sheet->setCellValue('B' . $row, $booking['user_name']);
    $sheet->setCellValue('C' . $row, $booking['room_name']);
    $sheet->setCellValue('D' . $row, $booking['booking_date']);
    $sheet->setCellValue('E' . $row, $booking['status']);
    $row++;
}

$filename = 'data_booking_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
