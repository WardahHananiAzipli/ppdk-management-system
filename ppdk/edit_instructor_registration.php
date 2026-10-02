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
$programs = [];
if (!empty($staffData['staff_id'])) {
    $stmtProgram = $conn->prepare("SELECT * FROM staff_program_involvement WHERE staff_id = ?");
    $stmtProgram->bind_param("i", $staffData['staff_id']);
    $stmtProgram->execute();
    $resultProgram = $stmtProgram->get_result();

    while ($row = $resultProgram->fetch_assoc()) {
        $programs[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laman Utama Petugas</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/registration.css">
  <link rel="stylesheet" href="css/add_row.css">
  <link rel="stylesheet" href="css/scroll.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body>
  <!-- Header -->
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

      <form method="POST" action="instructor_registration_process.php" enctype="multipart/form-data">
        <table class="form-table">
          <tr>
            <td>1. Nama</td>
            <td>: <input type="text" name="name" value="<?= htmlspecialchars($staffData['name']) ?>"></td>
            <td rowspan="12" class="photo">
              <?php if (!empty($staffData['profile_pic'])): ?>
                <img src="<?= htmlspecialchars($staffData['profile_pic']) ?>" alt="Gambar Profil" class="photo-img"><br><br>
              <?php else: ?>
                <div class="photo-img icon-fallback">
                  <i class="fas fa-user"></i>
                </div><br><br>
              <?php endif; ?>
              <label for="profile_pic">Tukar Gambar:</label>
              <input type="file" name="profile_pic" id="profile_pic">
            </td>
          </tr>
          <tr><td>2. No Kad Pengenalan</td><td>: <input type="text" name="ic" value="<?= $staffData['ic'] ?>"></td></tr>
          <tr><td>3. Tarikh Lahir / Umur</td><td>: 
            <input type="date" name="dob" value="<?= $staffData['dob'] ?>"> /
            <input type="number" name="age" value="<?= $staffData['age'] ?>" style="width:60px;"> tahun
          </td></tr>
          <tr><td>4. Jantina</td><td>: 
            <select name="gender">
              <option value="">-- Sila Pilih --</option>
              <option <?= $staffData['gender'] == 'Lelaki' ? 'selected' : '' ?>>Lelaki</option>
              <option <?= $staffData['gender'] == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
            </select>
          </td></tr>
          <tr><td>5. Bangsa / Etnik</td><td>: <input type="text" name="race" value="<?= $staffData['race'] ?>"></td></tr>
          <tr><td>6. Agama</td><td>: <input type="text" name="religion" value="<?= $staffData['religion'] ?>"></td></tr>
          <tr><td>7. Status Perkahwinan</td><td>: 
            <select name="marital_status">
              <option value="">-- Sila Pilih --</option>
              <option <?= $staffData['marital_status'] == 'Bujang' ? 'selected' : '' ?>>Bujang</option>
              <option <?= $staffData['marital_status'] == 'Berkahwin' ? 'selected' : '' ?>>Berkahwin</option>
              <option <?= $staffData['marital_status'] == 'Duda/Janda' ? 'selected' : '' ?>>Duda/Janda</option>
            </select>
          </td></tr>
          <tr><td>8. Bil. Anak / Tanggungan</td><td>: <input type="number" name="dependents" value="<?= $staffData['dependents'] ?>"></td></tr>
          <tr><td>9. Alamat Rumah</td><td>: <textarea name="address"><?= $staffData['address'] ?></textarea></td></tr>
          <tr>
            <td>10. No. Telefon Rumah<br><br><span style="display:inline-block; margin-left: 25px;">No. Telefon Bimbit</span></td>
            <td>: <input type="text" name="home_phone" value="<?= $staffData['home_phone'] ?>"><br><br>
                : <input type="text" name="mobile_phone" value="<?= $staffData['mobile_phone'] ?>"></td>
          </tr>
          <tr><td>11. Alamat E-mel</td><td>: <input type="email" name="email" value="<?= $staffData['email'] ?>"></td></tr>
          <tr><td>12. Pendapatan Isi Rumah</td><td>: RM <input type="number" name="household_income" value="<?= $staffData['household_income'] ?>"></td></tr>
          <tr><td>13. Pekerjaan sebelum ini</td><td>: <input type="text" name="prev_occupation" value="<?= $staffData['prev_occupation'] ?>"></td></tr>
          <tr><td>14. Pengalaman berkaitan OKU <br> (Nyatakan tempoh dan perincian pengalaman)
          </td><td>: <textarea name="exp_with_pwd"><?= $staffData['exp_with_pwd'] ?></textarea></td></tr>
        </table>

        <h4>B. Penglibatan dalam Program OKU / Kemasyarakatan</h4>
        <table class="program-table">
          <thead>
            <tr><th>Bil</th><th>Nama Program / Pertubuhan</th><th>Jawatan</th><th>Tahun</th></tr>
          </thead>
          <tbody id="program-table-body">
            <?php if (!empty($programs)): ?>
              <?php foreach ($programs as $index => $program): ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><input type="text" name="program_name[]" value="<?= htmlspecialchars($program['program_name']) ?>" class="form-control"></td>
                  <td><input type="text" name="position_held[]" value="<?= htmlspecialchars($program['position_held']) ?>" class="form-control"></td>
                  <td><input type="text" name="year[]" value="<?= htmlspecialchars($program['year']) ?>" class="form-control"></td>
                  <td><button type="button" class="btn-remove-row">✖</button></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td>1</td>
                <td><input type="text" name="program_name[]" class="form-control"></td>
                <td><input type="text" name="position_held[]" class="form-control"></td>
                <td><input type="text" name="year[]" class="form-control"></td>
                <td><button type="button" class="btn-remove-row">✖</button></td>
              </tr>
            <?php endif; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="5">
                <button type="button" class="btn-add-row" onclick="addProgramRow()">+ Tambah Program</button>
              </td>
            </tr>
          </tfoot>
          </tbody>

        </table>

        <div class="form-button">
            <button type="submit" class="btn-save">Simpan Borang</button>
            <a href="instructor_registration.php" class="btn-cancel">Batal</a>
        </div>
        </div>
      </form>
  </main>

  <!-- Scroll button -->
  <script src="js/add_row.js"></script>

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