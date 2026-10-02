<?php
$conn = new mysqli("localhost", "root", "", "ppdk_db");

$type = $_GET['type'] ?? '';
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';
$jantina = $_GET['jantina'] ?? '';

// Build WHERE filter
$where = "WHERE 1";
if ($bulan !== '') $where .= " AND MONTH(registration_date) = " . (int)$bulan;
if ($tahun !== '') $where .= " AND YEAR(registration_date) = " . (int)$tahun;
if ($jantina !== '') $where .= " AND gender = '" . $conn->real_escape_string($jantina) . "'";

if ($type == 'table') {
    $query = "SELECT staff_id, name, gender, registration_date FROM staff $where ORDER BY registration_date DESC";
    $result = $conn->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['data' => $data]);
}

elseif ($type == 'month') {
    $query = "SELECT MONTH(registration_date) AS m, COUNT(*) AS jumlah FROM staff $where GROUP BY m";
    $result = $conn->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = ['label' => date('F', mktime(0,0,0,$row['m'],10)), 'value' => (int)$row['jumlah']];
    }
    echo json_encode($data);
}

elseif ($type == 'year') {
    $query = "SELECT YEAR(registration_date) AS y, COUNT(*) AS jumlah FROM staff $where GROUP BY y";
    $result = $conn->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = ['label' => $row['y'], 'value' => (int)$row['jumlah']];
    }
    echo json_encode($data);
}

elseif ($type == 'gender') {
    $query = "SELECT gender, COUNT(*) AS jumlah FROM staff $where GROUP BY gender";
    $result = $conn->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = ['label' => $row['gender'], 'value' => (int)$row['jumlah']];
    }
    echo json_encode($data);
}

elseif ($type == 'total') {
    $total = $conn->query("SELECT COUNT(*) AS total FROM staff $where")->fetch_assoc()['total'];
    $male = $conn->query("SELECT COUNT(*) AS total FROM staff $where AND gender='Lelaki'")->fetch_assoc()['total'];
    $female = $conn->query("SELECT COUNT(*) AS total FROM staff $where AND gender='Perempuan'")->fetch_assoc()['total'];
    echo json_encode(['total' => (int)$total, 'male' => (int)$male, 'female' => (int)$female]);
}
