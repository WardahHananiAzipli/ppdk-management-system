<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ppdk_db');
$username = $_SESSION['username'];

// Fetch from users table
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$user_id = $data['user_id']; // Ambil ID user

$roleMalay = [
  'instructor' => 'Petugas',
  'supervisor' => 'Penyelia',
  'chairman'   => 'Pengerusi'
];

$profilePic = !empty($data['profile_pic']) ? 'uploads/' . $data['profile_pic'] : 'img/profile-placeholder.jpg';
$updateSuccess = isset($_GET['update']) && $_GET['update'] === 'success';

// Fetch from staff table
$queryStaff = "SELECT * FROM staff WHERE user_id = ?";
$stmtStaff = $conn->prepare($queryStaff);
$stmtStaff->bind_param("i", $user_id);
$stmtStaff->execute();
$resultStaff = $stmtStaff->get_result();
$staffData = $resultStaff->fetch_assoc();
$staff_id = $staffData ? $staffData['staff_id'] : null;

// If no staff record found, assign default empty values
if (!$staffData) {
    $staffData = [
        'name' => '',
        'ic' => '',
        'dob' => '',
        'age' => '',
        'gender' => '',
        'race' => '',
        'religion' => '',
        'marital_status' => '',
        'dependents' => '',
        'address' => '',
        'home_phone' => '',
        'mobile_phone' => '',
        'email' => '',
        'household_income' => '',
        'prev_occupation' => '',
        'exp_with_pwd' => '',
        'profile_pic' => '',
    ];
}

// Fetch from staff_program_involvement table
$queryInvolvement = "SELECT * FROM staff_program_involvement WHERE staff_id = ?";
$stmtInvolvement = $conn->prepare($queryInvolvement);
$stmtInvolvement->bind_param("s", $staff_id);
$stmtInvolvement->execute();
$resultInvolvement = $stmtInvolvement->get_result();

$involvements = [];
while ($row = $resultInvolvement->fetch_assoc()) {
    $involvements[] = $row;
}

?>

<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pendaftaran Petugas</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/registration.css">
  <link rel="stylesheet" href="css/scroll.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
<header>
  <img src="img/logo.jpg" alt="PDK INSANI Logo">
  <h1>SISTEM PENGURUSAN PPDK INSANI</h1>
  <nav>
    <a href="login.php"><i class="fas fa-right-to-bracket"></i> <span>LOG KELUAR</span></a>
  </nav>
</header>

<!-- Sidebar -->
<div class="sidebar">
  <?php if (!empty($data['profile_pic'])): ?>
    <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture" class="sidebar-profile">
  <?php else: ?>
    <div class="sidebar-profile icon-fallback">
      <i class="fas fa-user"></i>
    </div>
  <?php endif; ?>

  <h1><?= $data['username']; ?></h1>
  <a href="index.php">Profil</a>
  <div class="dropdown">
    <button class="dropdown-btn">Pendaftaran</button>
    <div class="dropdown-content">
      <a href="instructor_registration.php">Petugas</a>
      <a href="trainee_registration.php">Pelatih</a>
    </div>
  </div>
  <div class="dropdown">
    <button class="dropdown-btn">Kehadiran</button>
    <div class="dropdown-content">
      <a href="instructor_attendance.php">Petugas</a>
      <a href="trainee_attendance.php">Pelatih</a>
    </div>
  </div>
      <a href="trainee_allowance.php">Elaun Pelatih</a>
      <a href="trainee_performance.php">Laporan Kemajuan Pelatih</a>
</div>

  <!-- Main body -->
  <main class="printable-form">

    <!-- Alert message -->
    <?php if ($updateSuccess): ?>
      <div class="alert-success">
        Maklumat berjaya dikemaskini.
      </div>
    <?php endif; ?>

      <div class="form-header">
        <div class="form-header-top">
          <div class="form-code-left">NO KOD :</div>
          <div class="form-code-right">BORANG PDK 3b / LAMPIRAN 4</div>
      </div>
      <div class="form-title">
        <h2>BORANG PENDAFTARAN PETUGAS</h2>
        <p><strong>PROGRAM PEMULIHAN DALAM KOMUNITI INSANI</strong></p>
        <h4 style="text-align: left;">A. BUTIRAN PERIBADI</h4>
        <p style="text-align: left;"><strong>NEGERI : KELANTAN</strong></p>
      </div>

      <table class="form-table">
        <tr>
          <td>1. Nama</td><td>: <?= $staffData['name']; ?></td>
          <td rowspan="12" class="photo">
            <?php if (!empty($staffData['profile_pic'])): ?>
              <img src="<?= htmlspecialchars($staffData['profile_pic']) ?>" alt="Gambar Profil" class="photo-img">
            <?php else: ?>
              <div class="photo-img icon-fallback">
                <i class="fas fa-user"></i>
              </div>
            <?php endif; ?>
          </td>
        </tr>
        <tr><td>2. No Kad Pengenalan</td><td>: <?= $staffData['ic']; ?></td></tr>
        <tr><td>3. Tarikh Lahir / Umur</td><td>: <?= $staffData['dob']; ?> / <?= $staffData['age']; ?> tahun</td></tr>
        <tr><td>4. Jantina</td><td>: <?= $staffData['gender']; ?></td></tr>
        <tr><td>5. Bangsa / Etnik</td><td>: <?= $staffData['race']; ?></td></tr>
        <tr><td>6. Agama</td><td>: <?= $staffData['religion']; ?></td></tr>
        <tr><td>7. Status Perkahwinan</td><td>: <?= $staffData['marital_status']; ?></td></tr>
        <tr><td>8. Bil. Anak / Tanggungan</td><td>: <?= $staffData['dependents']; ?></td></tr>
        <tr><td>9. Alamat Rumah</td><td>: <?= $staffData['address']; ?></td></tr>
        <tr><td>10. No. Telefon Rumah <br><br> <span style="display:inline-block; margin-left: 25px;">No. Telefon Bimbit</td><td>: <?= $staffData['home_phone']; ?></span> <br><br>: <?= $staffData['mobile_phone']; ?> </td></tr>
        <tr><td>11. Alamat E-mel</td><td>: <?= $staffData['email']; ?></td></tr>
        <tr><td>12. Pendapatan Isi Rumah</td><td>: RM <?= $staffData['household_income']; ?></td></tr>
        <tr><td>13. Pekerjaan sebelum ini</td><td>: <?= $staffData['prev_occupation']; ?></td></tr>
        <tr><td>14. Pengalaman berkaitan OKU <br> (Nyatakan tempoh dan perincian pengalaman)</td><td>: <?= $staffData['exp_with_pwd']; ?></td></tr>
      </table>

      <h4 class="page-break">B. Penglibatan dalam Program OKU / Kemasyarakatan</h4>
      <table class="program-table">
        <thead>
          <tr><th>Bil</th><th>Nama Program / Pertubuhan</th><th>Jawatan</th><th>Tahun</th></tr>
        </thead>
        <tbody>
          <?php if (!empty($involvements)): ?>
            <?php foreach ($involvements as $index => $inv): ?>
              <tr>
                <td><?= $index + 1; ?></td>
                <td><?= htmlspecialchars($inv['program_name']); ?></td>
                <td><?= htmlspecialchars($inv['position_held']); ?></td>
                <td><?= htmlspecialchars($inv['year']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td>1</td>
              <td colspan="3">Tiada penglibatan direkodkan.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

      <div class="form-button">
        <a href="edit_instructor_registration.php" class="btn-edit">Kemaskini Borang</a>
        <button onclick="window.print()" class="btn-print">Cetak Borang</button>
      </div>
    </div>
  </main>

  <!-- Scroll button -->
  <button id="scrollToTop" class="scroll-to-top"><i class="fas fa-arrow-up"></i></button>
  <script src="js/scroll.js"></script>

  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Pertubuhan Pemulihan Dalam Komuniti Insani Machang</p>
    <div class="social-icons">
        <a href="https://www.facebook.com/ppdkinsanikgrajamachang?mibextid=ZbWKwL" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.instagram.com/ppdki?igsh=Zm4waDU2Z2Q0b3gw" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
        <a href="https://www.tiktok.com/@ppdkinsani?_t=ZS-8xJNXvkebHU&_r=1" target="_blank" title="TikTok"><i class="fab fa-tiktok"></i></a>
    </div>
  </footer>

</body>
</html>