<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ppdk_db');
$username = $_SESSION['username'];

// Dapatkan user_id dan profile_pic dari jadual users
$getUser = $conn->prepare("SELECT user_id, profile_pic FROM users WHERE username = ?");
$getUser->bind_param("s", $username);
$getUser->execute();
$userResult = $getUser->get_result();
$userRow = $userResult->fetch_assoc();
$user_id = $userRow['user_id'];
$existingProfilePic = $userRow['profile_pic'] ?? '';
$getUser->close();

if (!$user_id) {
    die("Gagal: user_id tidak dijumpai.");
}

// Handle profile picture upload
$uploadDir = 'uploads/';
$uploadFile = '';
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
    $fileName = basename($_FILES['profile_pic']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileExt, $allowedTypes)) {
        $newFileName = uniqid('staff_') . '.' . $fileExt;
        $uploadFile = $uploadDir . $newFileName;
        move_uploaded_file($fileTmpPath, $uploadFile);

        if (!empty($existingProfilePic) && file_exists($existingProfilePic)) {
            unlink($existingProfilePic);
        }
    }
}

$finalProfilePic = $uploadFile ?: $existingProfilePic;

// Cek dulu sama ada staff dah wujud
$staff_id = null;
$checkStaff = $conn->prepare("SELECT staff_id FROM staff WHERE user_id = ?");
$checkStaff->bind_param("i", $user_id);
$checkStaff->execute();
$resultStaff = $checkStaff->get_result();
if ($row = $resultStaff->fetch_assoc()) {
    $staff_id = $row['staff_id'];
}
$checkStaff->close();

if ($staff_id) {
    // Update staff
    $sql = "UPDATE staff SET 
        name=?, ic=?, dob=?, age=?, gender=?, race=?, religion=?, marital_status=?, dependents=?,
        address=?, home_phone=?, mobile_phone=?, email=?, household_income=?,
        prev_occupation=?, exp_with_pwd=?, profile_pic=?
        WHERE staff_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssssssssssssi",
        $_POST['name'],
        $_POST['ic'],
        $_POST['dob'],
        $_POST['age'],
        $_POST['gender'],
        $_POST['race'],
        $_POST['religion'],
        $_POST['marital_status'],
        $_POST['dependents'],
        $_POST['address'],
        $_POST['home_phone'],
        $_POST['mobile_phone'],
        $_POST['email'],
        $_POST['household_income'],
        $_POST['prev_occupation'],
        $_POST['exp_with_pwd'],
        $finalProfilePic,
        $staff_id
    );
    $stmt->execute();
} else {
    // Insert baru
    $sql = "INSERT INTO staff (
        user_id, name, ic, dob, age, gender, race, religion, marital_status, dependents,
        address, home_phone, mobile_phone, email, household_income,
        prev_occupation, exp_with_pwd, profile_pic
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isssssssssssssssss",
        $user_id,
        $_POST['name'],
        $_POST['ic'],
        $_POST['dob'],
        $_POST['age'],
        $_POST['gender'],
        $_POST['race'],
        $_POST['religion'],
        $_POST['marital_status'],
        $_POST['dependents'],
        $_POST['address'],
        $_POST['home_phone'],
        $_POST['mobile_phone'],
        $_POST['email'],
        $_POST['household_income'],
        $_POST['prev_occupation'],
        $_POST['exp_with_pwd'],
        $finalProfilePic
    );
    $stmt->execute();
    $staff_id = $conn->insert_id;
}
$stmt->close();

// Handle program involvement
// Hapus dulu program lama
$conn->query("DELETE FROM staff_program_involvement WHERE staff_id = $staff_id");

// Masukkan program baru
if (!empty($_POST['program_name'])) {
    $progStmt = $conn->prepare("INSERT INTO staff_program_involvement (staff_id, program_name, position_held, year) VALUES (?, ?, ?, ?)");
    foreach ($_POST['program_name'] as $i => $program) {
        $position_held = $_POST['position_held'][$i] ?? '';
        $year = $_POST['year'][$i] ?? '';
        if (!empty($program)) {
            $progStmt->bind_param("isss", $staff_id, $program, $position_held, $year);
            $progStmt->execute();
        }
    }
    $progStmt->close();
}

header("Location: supervisor_registration.php?save=success");
exit;
?>
