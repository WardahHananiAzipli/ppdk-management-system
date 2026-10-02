<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ppdk_db');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$userStmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$userStmt->bind_param("s", $username);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

$user_id = $user['user_id']; // Ambil ID user

$roleMalay = [
  'instructor' => 'Petugas',
  'supervisor' => 'Penyelia',
  'chairman'   => 'Pengerusi'
];

$profilePic = !empty($user['profile_pic']) ? 'uploads/' . $user['profile_pic'] : 'img/profile-placeholder.jpg';
$updateSuccess = isset($_GET['update']) && $_GET['update'] === 'success';

// Dapatkan staff_id pengguna login
$staffStmt = $conn->prepare("SELECT * FROM staff WHERE user_id = ?");
$staffStmt->bind_param("i", $user_id);
$staffStmt->execute();
$staffLoggedIn = $staffStmt->get_result()->fetch_assoc();
$loggedInStaffId = $staffLoggedIn['staff_id'];

$bulan = $_GET['bulan'] ?? date('n');
$tahun = $_GET['tahun'] ?? date('Y');
$hariIni = date('j');
$tarikhHariIni = date('Y-m-d');

// Dapatkan semua staff
$allStaff = $conn->query("SELECT * FROM staff");

function singkatanHari($tarikh) {
    $hari = date('w', strtotime($tarikh));
    return ['A', 'I', 'S', 'R', 'K', 'J', 'S'][$hari];
}

function jumlahHariDalamBulan($bulan, $tahun) {
    return cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
}

function namaBulanMelayu($bulan) {
    $bulan_melayu = [
        1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MAC', 4 => 'APRIL',
        5 => 'MEI', 6 => 'JUN', 7 => 'JULAI', 8 => 'OGOS',
        9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DISEMBER'
    ];
    return $bulan_melayu[$bulan] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kehadiran Petugas</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/attendance.css">
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
  <?php if (!empty($user['profile_pic'])): ?>
    <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture" class="sidebar-profile">
  <?php else: ?>
    <div class="sidebar-profile icon-fallback">
      <i class="fas fa-user"></i>
    </div>
  <?php endif; ?>

  <h1><?= $user['username']; ?></h1>
  <a href="ch_index.php">Profil</a>
  <div class="dropdown">
    <button class="dropdown-btn">Pendaftaran</button>
    <div class="dropdown-content">
      <a href="ch_instructor_registration.php">Petugas</a>
      <a href="ch_trainee_registration.php">Pelatih</a>
    </div>
  </div>
  <div class="dropdown">
    <button class="dropdown-btn">Kehadiran</button>
    <div class="dropdown-content">
      <a href="ch_instructor_attendance.php">Petugas</a>
      <a href="ch_trainee_attendance.php">Pelatih</a>
    </div>
  </div>
      <a href="ch_trainee_allowance.php">Elaun Pelatih</a>
      <a href="ch_trainee_performance.php">Laporan Kemajuan Pelatih</a>
</div>

<!-- Main -->
<div class="main-content">

<div class="form-container">
  <form method="get">
    <label for="bulan">Bulan:</label>
    <select name="bulan" id="bulan">
      <?php for ($i = 1; $i <= 12; $i++): ?>
        <option value="<?= $i ?>" <?= ($i == $bulan) ? 'selected' : '' ?>>
          <?= namaBulanMelayu($i) ?>
        </option>
      <?php endfor; ?>
    </select>

    <label for="tahun">Tahun:</label>
    <input type="number" name="tahun" id="tahun" value="<?= $tahun ?>" min="2000" max="2100">
    <button type="submit">Pilih</button>
  </form>

  <form method="post" action="instructor_attendance_process.php">
    <input type="hidden" name="bulan" value="<?= $bulan ?>">
    <input type="hidden" name="tahun" value="<?= $tahun ?>">

    <h2>PERTUBUHAN PEMULIHAN DALAM KOMUNITI INSANI</h2>
    <h3>REKOD KEHADIRAN PETUGAS DI PUSAT BULAN <?= namaBulanMelayu($bulan) ?> <?= $tahun ?></h3>
  </form>
</div>

<div class="table-wrapper">
<!-- TABLE: 1 - 15 haribulan -->
<table>
  <thead>
    <tr>
      <th rowspan="2">Bil</th>
      <th rowspan="2">Nama</th>
      <th colspan="2">Jantina</th>
      <?php for ($day = 1; $day <= 15; $day++): ?>
        <?php if (checkdate($bulan, $day, $tahun)): ?>
          <th><?= $day ?></th>
        <?php endif; ?>
      <?php endfor; ?>
      <th rowspan="2">Jumlah</th>
    </tr>
    <tr>
      <th>L</th>
      <th>P</th>
      <?php for ($day = 1; $day <= 15; $day++): ?>
        <?php if (checkdate($bulan, $day, $tahun)): ?>
          <th><?= singkatanHari("$tahun-$bulan-$day") ?></th>
        <?php endif; ?>
      <?php endfor; ?>
    </tr>
  </thead>

  <tbody>
    <?php 
    $bil = 1; 
    $allStaff->data_seek(0);
    while ($staff = $allStaff->fetch_assoc()):
      $staff_id = $staff['staff_id'];
      $nama = $staff['name'];
      $jantina = $staff['gender'];
      $jantinaText = strtoupper(substr($jantina, 0, 1));
      $jumlahHadir = 0;
    ?>
    <tr>
      <td><?= $bil++ ?></td>
      <td class="info"><?= $nama ?></td>
      <td class="info" style="text-align: center;"><?= $jantinaText == 'L' ? '/' : '' ?></td>
      <td class="info" style="text-align: center;"><?= $jantinaText == 'P' ? '/' : '' ?></td>

      <?php for ($day = 1; $day <= 15; $day++):
        if (!checkdate($bulan, $day, $tahun)) continue;

        $tarikh = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);

        // Semak kehadiran
        $stmt = $conn->prepare("SELECT present FROM attendance WHERE staff_id = ? AND date = ?");
        $stmt->bind_param("is", $staff_id, $tarikh);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $hadir = $data && $data['present'] ? true : false;
        if ($hadir) $jumlahHadir++;

        $simbol = $hadir ? '✓' : '';
        echo "<td style='text-align:center;'>$simbol</td>";

      endfor; ?>

      <td><input type="number" class="total" value="<?= $jumlahHadir ?>" readonly></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<br>

<!-- TABLE: 16 - 31 haribulan -->
<table>
  <thead>
    <tr>
      <th rowspan="2">Bil</th>
      <th rowspan="2">Nama</th>
      <th colspan="2">Jantina</th>
      <?php for ($day = 16; $day <= 31; $day++): ?>
        <?php if (checkdate($bulan, $day, $tahun)): ?>
          <th><?= $day ?></th>
        <?php endif; ?>
      <?php endfor; ?>
      <th rowspan="2">Jumlah</th>
    </tr>
    <tr>
      <th>L</th>
      <th>P</th>
      <?php for ($day = 16; $day <= 31; $day++): ?>
        <?php if (checkdate($bulan, $day, $tahun)): ?>
          <th><?= singkatanHari("$tahun-$bulan-$day") ?></th>
        <?php endif; ?>
      <?php endfor; ?>
    </tr>
  </thead>

  <tbody>
    <?php 
    $bil = 1; 
    $allStaff->data_seek(0);
    while ($staff = $allStaff->fetch_assoc()):
      $staff_id = $staff['staff_id'];
      $nama = $staff['name'];
      $jantina = $staff['gender'];
      $jantinaText = strtoupper(substr($jantina, 0, 1));
      $jumlahHadir = 0;
    ?>
    <tr>
      <td><?= $bil++ ?></td>
      <td class="info"><?= $nama ?></td>
      <td class="info" style="text-align: center;"><?= $jantinaText == 'L' ? '/' : '' ?></td>
      <td class="info" style="text-align: center;"><?= $jantinaText == 'P' ? '/' : '' ?></td>

      <?php for ($day = 16; $day <= 31; $day++):
        if (!checkdate($bulan, $day, $tahun)) continue;

        $tarikh = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);

        // Semak kehadiran
              $stmt = $conn->prepare("SELECT present FROM attendance WHERE staff_id = ? AND date = ?");
              $stmt->bind_param("is", $staff_id, $tarikh);
              $stmt->execute();
              $data = $stmt->get_result()->fetch_assoc();
              $hadir = $data && $data['present'] ? true : false;
              if ($hadir) $jumlahHadir++;

              $simbol = $hadir ? '✓' : '';
              echo "<td style='text-align:center;'>$simbol</td>";

            endfor; ?>

      <td><input type="number" class="total" value="<?= $jumlahHadir ?>" readonly></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>


</div>
</form>
</div>

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
