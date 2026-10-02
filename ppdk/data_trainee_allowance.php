<?php
$conn = new mysqli("localhost", "root", "", "ppdk_db");

// Dapatkan parameter dari URL
$type = $_GET['type'] ?? '';
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';
$jantina = $_GET['jantina'] ?? '';
$trainee_id = $_GET['trainee_id'] ?? '';
$approval_status = $_GET['approval_status'] ?? '';

// Bina WHERE clause
$where = "WHERE 1";

if ($bulan !== '') {
    $where .= " AND a.month = " . (int)$bulan;
}
if ($tahun !== '') {
    $where .= " AND a.year = " . (int)$tahun;
}
if ($jantina !== '') {
    $where .= " AND s.gender = '" . $conn->real_escape_string($jantina) . "'";
}
if ($trainee_id !== '') {
    $where .= " AND s.trainee_id = '" . $conn->real_escape_string($trainee_id) . "'";
}
if ($approval_status !== '') {
    $where .= " AND a.approval_status = '" . $conn->real_escape_string($approval_status) . "'";
}

// ------------------------ JENIS: table ------------------------
if ($type == 'table') {
    $query = "
        SELECT 
            s.trainee_id, 
            s.full_name, 
            s.gender, 
            a.approval_status
            FROM trainee_allowance a
        JOIN trainee s ON a.trainee_id = s.trainee_id
        $where
        GROUP BY s.trainee_id, s.full_name, s.gender,  a.approval_status
        ORDER BY s.full_name ASC
    ";

    $result = $conn->query($query);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'trainee_id' => $row['trainee_id'],
            'full_name' => $row['full_name'],
            'gender' => $row['gender'],
            'approval_status' => $row['approval_status']
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}

// ------------------------ JENIS: month ------------------------
elseif ($type == 'month') {
    $query = "
        SELECT a.month AS m, SUM(a.attendance_count) AS jumlah
        FROM trainee_allowance a
        JOIN trainee s ON a.trainee_id = s.trainee_id
        $where
        GROUP BY a.month
        ORDER BY a.month
    ";

    $result = $conn->query($query);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'label' => date('F', mktime(0, 0, 0, $row['m'], 10)),
            'value' => (int)$row['jumlah']
        ];
    }

    echo json_encode($data);
    exit;
}

// ------------------------ JENIS: year ------------------------
elseif ($type == 'year') {
    $query = "
        SELECT a.year AS y, SUM(a.attendance_count) AS jumlah
        FROM trainee_allowance a
        JOIN trainee s ON a.trainee_id = s.trainee_id
        $where
        GROUP BY a.year
        ORDER BY a.year
    ";

    $result = $conn->query($query);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'label' => $row['y'],
            'value' => (int)$row['jumlah']
        ];
    }

    echo json_encode($data);
    exit;
}

// ------------------------ JENIS: gender ------------------------
elseif ($type == 'gender') {
    $query = "
        SELECT s.gender, SUM(a.attendance_count) AS jumlah
        FROM trainee_allowance a
        JOIN trainee s ON a.trainee_id = s.trainee_id
        $where
        GROUP BY s.gender
    ";

    $result = $conn->query($query);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'label' => $row['gender'],
            'value' => (int)$row['jumlah']
        ];
    }

    echo json_encode($data);
    exit;
}

// ------------------------ JENIS: total ------------------------
elseif ($type == 'total') {
    $total = $conn->query("
        SELECT COUNT(DISTINCT a.trainee_id) AS total 
        FROM trainee_allowance a 
        JOIN trainee s ON a.trainee_id = s.trainee_id 
        $where
    ")->fetch_assoc()['total'];

    echo json_encode([
        'total' => (int)$total
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
