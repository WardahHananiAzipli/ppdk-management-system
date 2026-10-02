<?php
$conn = new mysqli("localhost", "root", "", "ppdk_db");

// Dapatkan parameter dari URL
$type = $_GET['type'] ?? '';
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';
$jantina = $_GET['jantina'] ?? '';
$trainee_id = $_GET['trainee_id'] ?? '';
$status = $_GET['status'] ?? ''; // hadir = 1, tidak hadir = 0

// Bina WHERE clause untuk filter
$where = "WHERE 1";

if ($bulan !== '') {
    $where .= " AND MONTH(a.date) = " . (int)$bulan;
}

if ($tahun !== '') {
    $where .= " AND YEAR(a.date) = " . (int)$tahun;
}

if ($jantina !== '') {
    $where .= " AND s.gender = '" . $conn->real_escape_string($jantina) . "'";
}

if ($trainee_id !== '') {
    $where .= " AND s.trainee_id = '" . $conn->real_escape_string($trainee_id) . "'";
}

if ($status !== '') {
    $where .= " AND a.present = " . (int)$status;
}

// Jika jenis data = jadual utama
if ($type == 'table') {
    $query = "
        SELECT 
            s.trainee_id, 
            s.full_name, 
            s.gender, 
            COUNT(a.present) AS total_records,
            SUM(a.present) AS jumlah_kehadiran
        FROM attendance a
        JOIN trainee s ON a.trainee_id = s.trainee_id
        $where
        GROUP BY s.trainee_id, s.full_name, s.gender
        ORDER BY s.full_name ASC
    ";

    $result = $conn->query($query);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'trainee_id' => $row['trainee_id'],
            'full_name' => $row['full_name'],
            'gender' => $row['gender'],
            'jumlah_kehadiran' => (int) $row['jumlah_kehadiran']
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}

// Jika jenis data = bulan (untuk carta bar atau pie)
elseif ($type == 'month') {
    $query = "
        SELECT MONTH(a.date) AS m, COUNT(*) AS jumlah
        FROM attendance a
        JOIN trainee s ON a.trainee_id = s.trainee_id
        $where
        GROUP BY m
    ";
    $result = $conn->query($query);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'label' => date('F', mktime(0, 0, 0, $row['m'], 10)),
            'value' => (int) $row['jumlah']
        ];
    }

    echo json_encode($data);
    exit;
}

// Jika jenis data = tahun
elseif ($type == 'year') {
    $query = "
        SELECT YEAR(a.date) AS y, COUNT(*) AS jumlah
        FROM attendance a
        JOIN trainee s ON a.trainee_id = s.trainee_id
        $where
        GROUP BY y
    ";
    $result = $conn->query($query);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'label' => $row['y'],
            'value' => (int) $row['jumlah']
        ];
    }

    echo json_encode($data);
    exit;
}

// Jika jenis data = jantina
elseif ($type == 'gender') {
    $query = "
        SELECT s.gender, SUM(a.present) AS jumlah
        FROM attendance a
        JOIN trainee s ON a.trainee_id = s.trainee_id
        $where
        GROUP BY s.gender
    ";
    $result = $conn->query($query);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'label' => $row['gender'],
            'value' => (int) $row['jumlah']
        ];
    }

    echo json_encode($data);
    exit;
}

// Jika jenis data = total ringkasan
elseif ($type == 'total') {
    $total = $conn->query("SELECT COUNT(*) AS total FROM attendance a JOIN trainee s ON a.trainee_id = s.trainee_id $where")->fetch_assoc()['total'];
    $hadir = $conn->query("SELECT COUNT(*) AS total FROM attendance a JOIN trainee s ON a.trainee_id = s.trainee_id $where AND a.present = 1")->fetch_assoc()['total'];
    $tidak_hadir = $conn->query("SELECT COUNT(*) AS total FROM attendance a JOIN trainee s ON a.trainee_id = s.trainee_id $where AND a.present = 0")->fetch_assoc()['total'];

    echo json_encode([
        'total' => (int) $total,
        'hadir' => (int) $hadir,
        'tidak_hadir' => (int) $tidak_hadir
    ]);
    exit;
}

// Jika jenis data = senarai trainee berdasarkan jantina
elseif ($type == 'trainee_list') {
    $whereTrainee = "WHERE 1";
    if ($jantina !== '') {
        $whereTrainee .= " AND gender = '" . $conn->real_escape_string($jantina) . "'";
    }

    $result = $conn->query("SELECT trainee_id, full_name FROM trainee $whereTrainee ORDER BY full_name ASC");
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'trainee_id' => $row['trainee_id'],
            'full_name' => $row['full_name']
        ];
    }

    echo json_encode($data);
    exit;
}

?>
