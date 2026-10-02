<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ppdk_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $full_name = $_POST['full_name'] ?? '';
  $ic = $_POST['ic'] ?? '';
  $oku_num = $_POST['oku_num'] ?? '';
  $dob = $_POST['dob'] ?? '';
  $age = $_POST['age'] ?? '';
  $gender = $_POST['gender'] ?? '';
  $race = $_POST['race'] ?? '';
  $religion = $_POST['religion'] ?? '';
  $num_siblings = $_POST['num_siblings'] ?? '';
  $family_position = $_POST['family_position'] ?? '';
  $address = $_POST['address'] ?? '';
  $home_phone = $_POST['home_phone'] ?? '';
  $mobile_phone = $_POST['mobile_phone'] ?? '';
  $can_come_alone = $_POST['can_come_alone'] ?? '';
  $can_come_alone_note = $_POST['can_come_alone_note'] ?? '';
  $jkm_referral = $_POST['jkm_referral'] ?? '';
  $jkm_reason = $_POST['jkm_reason'] ?? '';
  $transport_method = isset($_POST['transport_method']) ? implode(',', $_POST['transport_method']) : '';

  $father_name = $_POST['father_name'] ?? '';
  $father_ic = $_POST['father_ic'] ?? '';
  $father_age = $_POST['father_age'] ?? '';
  $father_job = $_POST['father_job'] ?? '';
  $father_income = $_POST['father_income'] ?? '';
  $father_disability = $_POST['father_disability'] ?? '';
  $father_assistance = $_POST['father_assistance'] ?? '';
  $father_aid_type = $_POST['father_aid_type'] ?? '';
  $father_aid_amount = $_POST['father_aid_amount'] ?? '';
  $father_agency = $_POST['father_agency'] ?? '';

  $mother_name = $_POST['mother_name'] ?? '';
  $mother_ic = $_POST['mother_ic'] ?? '';
  $mother_age = $_POST['mother_age'] ?? '';
  $mother_job = $_POST['mother_job'] ?? '';
  $mother_income = $_POST['mother_income'] ?? '';
  $mother_disability = $_POST['mother_disability'] ?? '';
  $mother_assistance = $_POST['mother_assistance'] ?? '';
  $mother_aid_type = $_POST['mother_aid_type'] ?? '';
  $mother_aid_amount = $_POST['mother_aid_amount'] ?? '';
  $mother_agency = $_POST['mother_agency'] ?? '';

  $disability_level = $_POST['disability_level'] ?? '';
  $need_assistive = $_POST['need_assistive'] ?? '';
  $assistive_tool = $_POST['assistive_tool'] ?? '';
  $illness_type = $_POST['illness_type'] ?? '';
  $behaviour_array = $_POST['behaviour'] ?? [];
  if (in_array('Lain-lain', $behaviour_array) && !empty($_POST['behaviour_other'])) {
      $behaviour_array[] = $_POST['behaviour_other'];
  }
  $behaviour = json_encode($behaviour_array);
  $self_care = !empty($_POST['self_care']) ? json_encode($_POST['self_care']) : null;
  $mobility = !empty($_POST['mobility']) ? json_encode($_POST['mobility']) : null;


  $profile_pic = '';
  if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $profile_pic = $targetDir . basename($_FILES["profile_pic"]["name"]);
    move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $profile_pic);
  }

  $sql = "INSERT INTO trainee (
      full_name, ic, oku_num, dob, age, gender, race, religion, 
      num_siblings, family_position, address, home_phone, mobile_phone, 
      can_come_alone, can_come_alone_note, transport_method, 
      jkm_referral, jkm_reason, profile_pic, 
      father_name, father_ic, father_age, father_job, father_income, 
      father_disability, father_assistance, father_aid_type, father_aid_amount, father_agency,
      mother_name, mother_ic, mother_age, mother_job, mother_income, 
      mother_disability, mother_assistance, mother_aid_type, mother_aid_amount, mother_agency,
      disability_level, need_assistive, assistive_tool, illness_type, 
      behaviour, self_care, mobility
  ) VALUES (
      ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
      ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
      ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
      ?, ?, ?, ?, ?, ?, ?
  )";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssssisssiisssssssssssisisssssssisissssssssssss", 
      $full_name, $ic, $oku_num, $dob, $age, $gender, $race, $religion, 
      $num_siblings, $family_position, $address, $home_phone, $mobile_phone, 
      $can_come_alone, $can_come_alone_note, $transport_method, 
      $jkm_referral, $jkm_reason, $profile_pic, 
      $father_name, $father_ic, $father_age, $father_job, $father_income, 
      $father_disability, $father_assistance, $father_aid_type, $father_aid_amount, $father_agency,
      $mother_name, $mother_ic, $mother_age, $mother_job, $mother_income, 
      $mother_disability, $mother_assistance, $mother_aid_type, $mother_aid_amount, $mother_agency,
      $disability_level, $need_assistive, $assistive_tool, $illness_type, 
      $behaviour, $self_care, $mobility
  );

  if ($stmt->execute()) {
    $trainee_id = $conn->insert_id; // ✅ Dapatkan ID peserta terakhir
    header("Location: view_trainee_registration.php?id=" . $trainee_id); // ✅ Redirect ke halaman view
    exit();
  } else {
    echo "Ralat: " . $stmt->error;
  }

  $stmt->close();
  $conn->close();
}
?>
