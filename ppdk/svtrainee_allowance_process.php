<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ppdk_db");

// Semak login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Dapatkan username dan cari nama penuh penyelia dari jadual users + staff
$username = $_SESSION['username'];
$query = "
    SELECT staff.name AS nama_penyelia 
    FROM users 
    JOIN staff ON users.user_id = staff.user_id 
    WHERE users.username = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$disemak_oleh = $user['nama_penyelia'] ?? '';

// Semak jika ada data POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // =====================
    // A. Satu pelatih: proses kelulusan individu
    // =====================
    if (isset($_POST['trainee_id'], $_POST['bulan'], $_POST['tahun'], $_POST['approval_status'])) {
        $trainee_id = $_POST['trainee_id'];
        $bulan = $_POST['bulan'];
        $tahun = $_POST['tahun'];
        $approval_status = $_POST['approval_status'];

        // Semak sama ada rekod sudah wujud
        $checkQuery = "SELECT * FROM trainee_allowance WHERE trainee_id = ? AND month = ? AND year = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("iii", $trainee_id, $bulan, $tahun);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Kemaskini rekod
            $updateQuery = "UPDATE trainee_allowance 
                SET approval_status = ?, reviewed_by = ?, created_at = NOW() 
                WHERE trainee_id = ? AND month = ? AND year = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ssiii", $approval_status, $disemak_oleh, $trainee_id, $bulan, $tahun);
            $updateStmt->execute();
        } else {
            // Masukkan rekod baru
            $insertQuery = "INSERT INTO trainee_allowance (trainee_id, month, year, approval_status, reviewed_by) 
                VALUES (?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("iiiss", $trainee_id, $bulan, $tahun, $approval_status, $disemak_oleh);
            $insertStmt->execute();
        }

        // Kemaskini reviewed_by dalam attendance juga
        if (!empty($disemak_oleh)) {
            $updateAttendance = $conn->prepare("
                UPDATE attendance 
                SET reviewed_by = ? 
                WHERE trainee_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
            ");
            $updateAttendance->bind_param("siii", $disemak_oleh, $trainee_id, $bulan, $tahun);
            $updateAttendance->execute();
        }

        $_SESSION['success'] = "Status kelulusan telah dikemaskini dan disemak oleh $disemak_oleh.";
        header("Location: svtrainee_allowance.php?bulan=$bulan&tahun=$tahun");
        exit;
    }

    // =====================
    // B. Hantar semua kehadiran kepada petugas
    // =====================
    if (isset($_POST['hantar_petugas'], $_POST['disemak_oleh'], $_POST['bulan'], $_POST['tahun'])) {
        $bulan = $_POST['bulan'];
        $tahun = $_POST['tahun'];
        $disemak_oleh = $_POST['disemak_oleh'];

        if (!empty($disemak_oleh)) {
            // Kemas kini reviewed_by dalam attendance untuk semua pelatih bulan tersebut
            $update = $conn->prepare("
                UPDATE attendance 
                SET reviewed_by = ? 
                WHERE MONTH(date) = ? AND YEAR(date) = ?
            ");
            $update->bind_param("sii", $disemak_oleh, $bulan, $tahun);
            $update->execute();

            $_SESSION['success'] = "Kehadiran bulan $bulan/$tahun berjaya dihantar dan disemak oleh: $disemak_oleh.";
        } else {
            $_SESSION['success'] = "Sila pilih nama penyelia sebelum menghantar.";
        }

        header("Location: svtrainee_allowance.php?bulan=$bulan&tahun=$tahun");
        exit;
    }
}

// Jika sampai sini tanpa POST yang sah
$_SESSION['success'] = "Tiada tindakan dilakukan.";
header("Location: svtrainee_allowance.php");
exit;
?>
