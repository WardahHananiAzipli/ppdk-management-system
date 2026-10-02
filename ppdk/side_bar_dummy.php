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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laman Utama Petugas</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/scroll.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .sidebar {
      width: 250px;
      color: white;
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      overflow-x: auto;
      transition: all 0.3s ease;
      z-index: 1000;
    }

    .sidebar.hidden {
      display: none;
    }

    .sidebar.collapsed {
      width: 60px;
    }

    .sidebar .profile,
    .sidebar a,
    .sidebar .name {
      transition: opacity 0.3s;
    }

    .sidebar.collapsed .profile,
    .sidebar.collapsed .name,
    .sidebar.collapsed a {
      opacity: 0;
      pointer-events: none;
    }

    .toggle-btn {
      background: green;
      border: none;
      font-size: 24px;
      color: red;
      margin: 10px;
      cursor: pointer;
      float: right;
      z-index: 1100;
      position: relative;
    }

    .sidebar.collapsed .toggle-btn {
      float: none;
      display: block;
      margin: 10px auto;
    }

    .main-content {
      margin-left: 260px;
      max-width: 1000px;
      padding: 20px;
      margin-right: 20px;
      transition: margin-left 0.3s;
    }

.main-content.full-width {
  margin-left: 5px;
  width: 100%;
  max-width: 1150px; /* Tetapkan had maksimum selepas sidebar tutup */
  margin: 0 auto;    /* Untuk tengah-kan */
}

.main-content.full-width .attendance-table-wrapper {
  width: 100%;
  max-width: 100%;
  overflow-x: auto;
}

.main-content.full-width .attendance-table {
  width: 100%;
  max-width: 100%;
}

    .main-content table {
      width: 100%;
      max-width: 100%;
      overflow-x: auto;
      border-collapse: collapse;
      margin-top: 20px;
    }

    .main-content th,
    .main-content td {
      padding: 8px;
      text-align: center;
      border: 1px solid #ccc;
    }

    .main-content input[type="checkbox"] {
      transform: scale(1.2);
    }

    .main-content input[type="number"] {
      width: 50px;
      text-align: center;
    }

    .attendance-table-wrapper {
      overflow-x: auto;
      max-width: 100%;
      background: white;
      border-radius: 8px;
      padding: 10px;
    }

    .attendance-table {
      width: max-content;
      border-collapse: collapse;
    }

    .attendance-table th,
    .attendance-table td {
      border: 1px solid #ccc;
      padding: 5px;
      text-align: center;
      min-width: 40px;
    }

    .attendance-table th:first-child,
    .attendance-table td:first-child {
      position: sticky;
      left: 0;
      background-color: #fff;
      z-index: 1;
    }

    .toggle-btn {
  background: green;
  border: none;
  font-size: 24px;
  color: white;
  margin: 10px 10px 10px 0;
  cursor: pointer;
  position: absolute;
  left: 10px;
  top: 15px;
  z-index: 1100;
}

header {
  padding-left: 60px; /* bagi ruang supaya logo tak kena butang */
  position: relative;
}

    @media (max-width: 768px) {
  .attendance-table th,
  .attendance-table td {
    font-size: 12px;
    padding: 3px;
  }

  .main-content {
    padding: 10px;
  }



}
  </style>
</head>
<body>
  <button id="toggleSidebar" class="toggle-btn">☰</button>
  <header>
    <img src="img/logo.jpg" alt="PDK INSANI Logo">
    <h1>SISTEM PENGURUSAN PPDK INSANI</h1>
    <nav>
      <a href="login.php"><i class="fas fa-right-to-bracket"></i> <span>LOG KELUAR</span></a>
    </nav>
  </header>

  <!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <?php if (!empty($user['profile_pic'])): ?>
      <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture" class="sidebar-profile">
    <?php else: ?>
      <div class="sidebar-profile icon-fallback">
        <i class="fas fa-user"></i>
      </div>
    <?php endif; ?>

    <h1><?= $user['username']; ?></h1>
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
    <a href="trainee_performance.php">Laporan Kemajuan Pelatih</a>
    <a href="trainee_ass.php">Laporan Penilaian Pelatih</a>
  </div>

<!-- Main -->
<div class="main-content">
<h2>Rekod Kehadiran Bulanan</h2>

<form method="get">
    <label for="bulan">Bulan:</label>
    <select name="bulan" id="bulan">
        <?php for ($i = 1; $i <= 12; $i++): ?>
            <option value="<?= $i ?>" <?= ($i == $bulan) ? 'selected' : '' ?>>
                <?= date('F', mktime(0,0,0,$i,1)) ?>
            </option>
        <?php endfor; ?>
    </select>

    <label for="tahun">Tahun:</label>
    <input type="number" name="tahun" id="tahun" value="<?= $tahun ?>" min="2000" max="2100">
    <button type="submit">Tukar</button>
</form>

<form method="post" action="instructor_attendance_process.php">
    <input type="hidden" name="bulan" value="<?= $bulan ?>">
    <input type="hidden" name="tahun" value="<?= $tahun ?>">

    <div class="attendance-table-wrapper">
        <table class="attendance-table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Jantina</th>
                <?php for ($day = 1; $day <= 31; $day++): ?>
                    <?php if (checkdate($bulan, $day, $tahun)): ?>
                        <th><?= $day ?></th>
                    <?php endif; ?>
                <?php endfor; ?>
                <th>Jumlah Hadir</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($staff = $allStaff->fetch_assoc()): 
                $staff_id = $staff['staff_id'];
                $nama = $staff['name'];
                $jantina = $staff['gender'];

                // Check kalau dah tick hari ini
                $stmtCheckToday = $conn->prepare("SELECT present FROM attendance WHERE staff_id = ? AND date = ?");
                $stmtCheckToday->bind_param("is", $staff_id, $tarikhHariIni);
                $stmtCheckToday->execute();
                $alreadyTicked = $stmtCheckToday->get_result()->fetch_assoc();

                $jumlahHadir = 0;
            ?>
            <tr>
                <td class="info"><?= $nama ?></td>
                <td class="info"><?= $jantina ?></td>

                <?php
                for ($day = 1; $day <= 31; $day++):
                    if (!checkdate($bulan, $day, $tahun)) continue;

                    $tarikh = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);
                    $stmt = $conn->prepare("SELECT present FROM attendance WHERE staff_id = ? AND date = ?");
                    $stmt->bind_param("is", $staff_id, $tarikh);
                    $stmt->execute();
                    $data = $stmt->get_result()->fetch_assoc();
                    $hadir = $data && $data['present'] ? true : false;

                    $isToday = ($day == $hariIni && $bulan == date('n') && $tahun == date('Y'));
                    $canTick = ($staff_id == $loggedInStaffId && $isToday && !$alreadyTicked);

                    $checkbox = "<input type='checkbox' name='attendance[$staff_id][$day]' value='1' " .
                        ($hadir ? 'checked' : '') .
                        ($canTick ? '' : ' disabled') . ">";

                    if ($hadir) $jumlahHadir++;

                    echo "<td>$checkbox</td>";
                endfor;
                ?>
                <td><input type="number" value="<?= $jumlahHadir ?>" readonly></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>

    <br>
    <button type="submit">Simpan Kehadiran Saya</button>
</form>
</div>

  <script>
    const toggleBtn = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.querySelector(".main-content");

    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("hidden");
      mainContent.classList.toggle("full-width");
    });
  </script>



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
