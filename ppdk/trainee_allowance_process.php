<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ppdk_db');

if (!isset($_POST['trainee_id'], $_POST['bulan'], $_POST['tahun'])) {
    header("Location: trainee_allowance.php");
    exit;
}

$trainee_id = (int) $_POST['trainee_id'];
$bulan = (int) $_POST['bulan'];
$tahun = (int) $_POST['tahun'];

$startDate = sprintf('%04d-%02d-01', $tahun, $bulan);
$endDate = sprintf('%04d-%02d-31', $tahun, $bulan);

// Semak kehadiran pelatih ini
$stmt = $conn->prepare("
    SELECT COUNT(*) AS jumlah_hadir
    FROM attendance
    WHERE trainee_id = ? AND present = 1 AND date BETWEEN ? AND ?
");
$stmt->bind_param("iss", $trainee_id, $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$hadir = (int) $row['jumlah_hadir'];

if ($hadir >= 8) {
    // Semak sama ada sudah wujud
    $checkStmt = $conn->prepare("SELECT * FROM trainee_allowance WHERE trainee_id = ? AND month = ? AND year = ?");
    $checkStmt->bind_param("iii", $trainee_id, $bulan, $tahun);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        // Insert rekod baru
        $insertStmt = $conn->prepare("INSERT INTO trainee_allowance (trainee_id, month, year, attendance_count) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("iiii", $trainee_id, $bulan, $tahun, $hadir);
        $insertStmt->execute();
        $insertStmt->close();

        echo "<script>alert('Permohonan elaun berjaya dihantar.'); window.location.href = 'trainee_allowance.php?bulan=$bulan&tahun=$tahun';</script>";
    } else {
        echo "<script>alert('Permohonan elaun bagi pelatih ini telah direkodkan sebelum ini.'); window.location.href = 'trainee_allowance.php?bulan=$bulan&tahun=$tahun';</script>";
    }

    $checkStmt->close();
} else {
    echo "<script>alert('Pelatih belum cukup minimum 8 kali kehadiran.'); window.location.href = 'trainee_allowance.php?bulan=$bulan&tahun=$tahun';</script>";
}

$stmt->close();
$conn->close();
exit;
