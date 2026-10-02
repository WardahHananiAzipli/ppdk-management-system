<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ppdk_db');

$bulan = $_POST['bulan'];
$tahun = $_POST['tahun'];
$attendanceData = $_POST['attendance'] ?? [];
$today = date('Y-m-d');

foreach ($attendanceData as $staff_id => $days) {
    foreach ($days as $day => $value) {
        if (!checkdate($bulan, $day, $tahun)) continue;

        // hanya benarkan tick hari ini
        $date = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);
        if ($date != $today) continue;

        $present = 1;

        $stmt = $conn->prepare("
            INSERT INTO attendance (staff_id, date, present)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE present = VALUES(present)
        ");
        $stmt->bind_param("isi", $staff_id, $date, $present);
        $stmt->execute();
    }
}

header("Location: instructor_attendance.php?bulan=$bulan&tahun=$tahun");
