<?php
$conn = new mysqli('localhost', 'root', '', 'ppdk_db');

// Handle AJAX request (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'fetch') {
    $id = $_GET['id'] ?? '';
    $stmt = $conn->prepare("SELECT ic, dob, gender FROM trainee WHERE trainee_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    echo json_encode($data);
    exit;
}

// Handle form submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainee_id = $_POST['trainee_id'];
    $oku_categories = isset($_POST['oku_categories']) ? implode(",", $_POST['oku_categories']) : '';
    $assistive_tools_used = isset($_POST['assistive_tools_used']) ? implode(",", $_POST['assistive_tools_used']) : '';
    $assistive_tools_needed = $_POST['assistive_tools_needed'] ?? '';

    // Simpan ke table trainee
    $stmt = $conn->prepare("UPDATE trainee SET oku_categories = ?, assistive_tools_used = ?, assistive_tools_needed = ? WHERE trainee_id = ?");
    $stmt->bind_param("sssi", $oku_categories, $assistive_tools_used, $assistive_tools_needed, $trainee_id);
    $stmt->execute();

    echo "<script>alert('Maklumat berjaya disimpan!'); window.location.href='trainee_performance.php';</script>";
}
?>

<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ppdk_db');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainee_id = $_POST['trainee_id'];
    $motor_skills = $_POST['motor_skill'];

    // Ambil semua nama skill dari input
    $all_skills = array_keys($motor_skills);

    for ($week = 1; $week <= 4; $week++) {
        $hasData = false;
        $columns = ['trainee_id', 'week'];
        $placeholders = ['?', '?'];
        $types = 'ii';
        $values = [$trainee_id, $week];

        foreach ($motor_skills as $skill_name => $weeks) {
            $value = $weeks[$week] ?? null;

            $columns[] = $skill_name;
            $placeholders[] = '?';
            $types .= 's';
            $values[] = $value;

            if (!empty($value)) {
                $hasData = true; // Tanda ada input minggu ni
            }
        }

        // Simpan HANYA jika minggu tersebut ada data
        if ($hasData) {
            $sql = "INSERT INTO trainee_performance (" . implode(", ", $columns) . ")
                    VALUES (" . implode(", ", $placeholders) . ")";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$values);
            $stmt->execute();
        }
    }

    echo "<script>
        alert('Prestasi berjaya disimpan!');
        window.location.href='trainee_performance.php?trainee_id=$trainee_id';
    </script>";
    exit;
}
?>
