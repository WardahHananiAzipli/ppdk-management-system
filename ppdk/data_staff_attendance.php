<?php
$conn = new mysqli("localhost", "root", "", "ppdk_db");

// Dapatkan parameter dari URL
$type = $_GET['type'] ?? '';
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';
$jantina = $_GET['jantina'] ?? '';
$staff_id = $_GET['staff_id'] ?? '';
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

if ($staff_id !== '') {
    $where .= " AND s.staff_id = '" . $conn->real_escape_string($staff_id) . "'";
}

if ($status !== '') {
    $where .= " AND a.present = " . (int)$status;
}

// Jika jenis data = jadual utama
if ($type == 'table') {
    $query = "
        SELECT 
            s.staff_id, 
            s.name, 
            s.gender, 
            COUNT(a.present) AS total_records,
            SUM(a.present) AS jumlah_kehadiran
        FROM attendance a
        JOIN staff s ON a.staff_id = s.staff_id
        $where
        GROUP BY s.staff_id, s.name, s.gender
        ORDER BY s.name ASC
    ";

    $result = $conn->query($query);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'staff_id' => $row['staff_id'],
            'name' => $row['name'],
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
        JOIN staff s ON a.staff_id = s.staff_id
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
        JOIN staff s ON a.staff_id = s.staff_id
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
        JOIN staff s ON a.staff_id = s.staff_id
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
    $total = $conn->query("SELECT COUNT(*) AS total FROM attendance a JOIN staff s ON a.staff_id = s.staff_id $where")->fetch_assoc()['total'];
    $hadir = $conn->query("SELECT COUNT(*) AS total FROM attendance a JOIN staff s ON a.staff_id = s.staff_id $where AND a.present = 1")->fetch_assoc()['total'];
    $tidak_hadir = $conn->query("SELECT COUNT(*) AS total FROM attendance a JOIN staff s ON a.staff_id = s.staff_id $where AND a.present = 0")->fetch_assoc()['total'];

    echo json_encode([
        'total' => (int) $total,
        'hadir' => (int) $hadir,
        'tidak_hadir' => (int) $tidak_hadir
    ]);
    exit;
}

// Jika jenis data = senarai staff berdasarkan jantina
elseif ($type == 'staff_list') {
    $whereStaff = "WHERE 1";
    if ($jantina !== '') {
        $whereStaff .= " AND gender = '" . $conn->real_escape_string($jantina) . "'";
    }

    $result = $conn->query("SELECT staff_id, name FROM staff $whereStaff ORDER BY name ASC");
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'staff_id' => $row['staff_id'],
            'name' => $row['name']
        ];
    }

    echo json_encode($data);
    exit;
}

?>
