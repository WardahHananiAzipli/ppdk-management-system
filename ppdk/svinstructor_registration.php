<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ppdk_db');
$username = $_SESSION['username'];

// Dapatkan data pengguna
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$user_id = $data['user_id'];
$profilePic = !empty($data['profile_pic']) ? 'uploads/' . $data['profile_pic'] : 'img/profile-placeholder.jpg';

$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n'); // n = 1 to 12
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$bulanMalay = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Mac',
    4 => 'April',
    5 => 'Mei',
    6 => 'Jun',
    7 => 'Julai',
    8 => 'Ogos',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Disember'
];

$staff_id = $_GET['staff_id'] ?? '';

$staffOptions = [];

// Jika ada bulan & tahun, tapis ikut tarikh daftar
if (isset($_GET['month']) && isset($_GET['year'])) {
    $stmt = $conn->prepare("SELECT staff_id, name FROM staff WHERE MONTH(registration_date) = ? AND YEAR(registration_date) = ?");
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $staffOptions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    // Kalau belum tekan filter, ambil semua staff
    $stmt = $conn->prepare("SELECT staff_id, name FROM staff");
    $stmt->execute();
    $staffOptions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Dapatkan maklumat staff hanya jika staff_id dihantar
$staffData = null;
$involvements = [];

if (!empty($staff_id)) {
    $stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $staffData = $stmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("SELECT * FROM staff_program_involvement WHERE staff_id = ?");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $involvements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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

  <style>
    .form-header {
      border: 2px solid #ccc;
      padding: 20px;
      margin-bottom: 30px;
      margin-right: -100px;
      border-radius: 10px;
      background-color: #f9f9f9;
    }
  </style>
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
  <a href="sv_index.php">Profil</a>
  <div class="dropdown">
    <button class="dropdown-btn">Pendaftaran</button>
    <div class="dropdown-content">
      <a href="supervisor_registration.php">Penyelia</a>
      <a href="svinstructor_registration.php">Kakitangan</a>
      <a href="svtrainee_registration.php">Pelatih</a>
    </div>
  </div>
  <div class="dropdown">
    <button class="dropdown-btn">Kehadiran</button>
    <div class="dropdown-content">
      <a href="svninstructor_attendance.php">Kakitangan</a>
      <a href="svtrainee_attendance.php">Pelatih</a>
    </div>
  </div>
      <a href="svtrainee_allowance.php">Elaun Pelatih</a>
      <a href="svtrainee_performance.php">Laporan Kemajuan Pelatih</a>
</div>

<!-- Filter bulan, tahun -->
<div class="form-container" style="margin: 0 auto; width: 50%;">
<form method="GET">
  <label for="month">Bulan:</label>
  <select name="month" id="month" required>
    <?php for ($m = 1; $m <= 12; $m++): ?>
      <option value="<?= $m ?>" <?= ($m == $month) ? 'selected' : '' ?>>
        <?= $bulanMalay[$m] ?>      
      </option>
    <?php endfor; ?>
  </select>

  <label for="year">Tahun:</label>
  <select name="year" id="year" required>
    <?php for ($y = 2020; $y <= date('Y'); $y++): ?>
      <option value="<?= $y ?>" <?= ($y == $year) ? 'selected' : '' ?>><?= $y ?></option>
    <?php endfor; ?>
  </select>

  <button type="submit" class="btn-choose">Pilih</button>
</form>
</div>

  <!-- Satu Petugas -->
  <div class="form-container" style="margin: 0 auto; width: 50%;">
  <?php if (!empty($staffOptions)): ?>
    <form method="GET">
      <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">
      <input type="hidden" name="year" value="<?= htmlspecialchars($year) ?>">

      <label for="staff_id">Nama Petugas:</label>
      <select name="staff_id" id="staff_id" required>
        <option value="" disabled <?= empty($staff_id) ? 'selected' : '' ?>>---- Pilih Petugas ----</option>
        <?php foreach ($staffOptions as $s): ?>
          <option value="<?= $s['staff_id']; ?>" <?= ($s['staff_id'] == $staff_id) ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button type="submit" class="btn-choose">Pilih</button>
    </form>
  <?php endif; ?>
  </div>

  <hr>

  <!-- Main body -->
  <main class="printable-form">
  <?php if (!empty($staff_id) && $staffData): ?>
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
              <div class="photo-img icon-fallback"><i class="fas fa-user"></i></div>
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
        <tr><td>10. No. Telefon Rumah <br><br> <span style="margin-left:25px;">No. Telefon Bimbit</td><td>: <?= $staffData['home_phone']; ?> <br><br>: <?= $staffData['mobile_phone']; ?></span></td></tr>
        <tr><td>11. Alamat E-mel</td><td>: <?= $staffData['email']; ?></td></tr>
        <tr><td>12. Pendapatan Isi Rumah</td><td>: RM <?= $staffData['household_income']; ?></td></tr>
        <tr><td>13. Pekerjaan sebelum ini</td><td>: <?= $staffData['prev_occupation']; ?></td></tr>
        <tr><td>14. Pengalaman berkaitan OKU</td><td>: <?= $staffData['exp_with_pwd']; ?></td></tr>
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
            <tr><td>1</td><td colspan="3">Tiada penglibatan direkodkan.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <div class="form-button">
        <button onclick="window.print()" class="btn-print">Cetak Borang</button>
      </div>

    </div>

    <!-- Semua Petugas -->
    <?php elseif (empty($staff_id) && !empty($staffOptions)): ?>
      <?php foreach ($staffOptions as $s): ?>
        
        <?php
          $sid = $s['staff_id'];
          $stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
          $stmt->bind_param("i", $sid);
          $stmt->execute();
          $staffData = $stmt->get_result()->fetch_assoc();

          $stmt = $conn->prepare("SELECT * FROM staff_program_involvement WHERE staff_id = ?");
          $stmt->bind_param("i", $sid);
          $stmt->execute();
          $involvements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        ?>

        <div class="form-header page-break">
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
                  <div class="photo-img icon-fallback"><i class="fas fa-user"></i></div>
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
            <tr><td>10. No. Telefon Rumah <br><br> <span style="margin-left:25px;">No. Telefon Bimbit</span></td><td>: <?= $staffData['home_phone']; ?> <br><br>: <?= $staffData['mobile_phone']; ?></td></tr>
            <tr><td>11. Alamat E-mel</td><td>: <?= $staffData['email']; ?></td></tr>
            <tr><td>12. Pendapatan Isi Rumah</td><td>: RM <?= $staffData['household_income']; ?></td></tr>
            <tr><td>13. Pekerjaan sebelum ini</td><td>: <?= $staffData['prev_occupation']; ?></td></tr>
            <tr><td>14. Pengalaman berkaitan OKU</td><td>: <?= $staffData['exp_with_pwd']; ?></td></tr>
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
                <tr><td>1</td><td colspan="3">Tiada penglibatan direkodkan.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      <?php endforeach; ?>

    <?php elseif (!empty($staffOptions)): ?>
      <p><em>Sila pilih petugas dan tekan butang <strong>"Pilih"</strong> untuk paparkan maklumat.</em></p>

    <?php else: ?>
      <p><em>Tiada petugas didaftarkan bagi bulan dan tahun yang dipilih.</em></p>
    <?php endif; ?>

  </main>

</body>
</html>

