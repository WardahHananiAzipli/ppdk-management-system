<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ppdk_db');

$bulan = $_POST['bulan'];
$tahun = $_POST['tahun'];
$attendanceData = $_POST['attendance'] ?? [];
$today = date('Y-m-d');

// Ambil username dari session
$username = $_SESSION['username'];

// Cari user_id berdasarkan username
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result()->fetch_assoc();
$user_id = $userResult['user_id'] ?? null;

$staff_id = null;
if ($user_id) {
    // Cari staff_id berdasarkan user_id
    $stmt = $conn->prepare("SELECT staff_id FROM staff WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $staffResult = $stmt->get_result()->fetch_assoc();
    $staff_id = $staffResult['staff_id'] ?? null;
}

// Jika berjaya dapatkan staff_id (instructor), baru teruskan simpan kehadiran
if ($staff_id) {
    foreach ($attendanceData as $trainee_id => $days) {
        foreach ($days as $day => $value) {
            if (!checkdate($bulan, $day, $tahun)) continue;

            $date = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);
            if ($date != $today) continue;

            $present = 1;

            // Pastikan hanya masukkan jika belum wujud (sekali tick saja per hari)
            $checkStmt = $conn->prepare("SELECT * FROM attendance WHERE trainee_id = ? AND date = ?");
            $checkStmt->bind_param("is", $trainee_id, $date);
            $checkStmt->execute();
            $exists = $checkStmt->get_result()->num_rows > 0;

            if (!$exists) {
                $stmt = $conn->prepare("
                    INSERT INTO attendance (trainee_id, date, present, staff_id)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->bind_param("isii", $trainee_id, $date, $present, $staff_id);
                $stmt->execute();
            }
        }
    }
}

header("Location: trainee_attendance.php?bulan=$bulan&tahun=$tahun");
