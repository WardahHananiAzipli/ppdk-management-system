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

$user_id = $user['user_id'];
$profilePic = !empty($user['profile_pic']) ? 'uploads/' . $user['profile_pic'] : 'img/profile-placeholder.jpg';

// Dapatkan bulan & tahun
$bulan = $_GET['bulan'] ?? date('n');
$tahun = $_GET['tahun'] ?? date('Y');
$tarikhMula = sprintf('%04d-%02d-01', $tahun, $bulan);
$tarikhAkhir = sprintf('%04d-%02d-31', $tahun, $bulan);

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

// Query pelatih layak
$sql = "
SELECT 
  t.trainee_id, 
  t.full_name, 
  ta.approval_status,
  COUNT(*) AS jumlah_hadir
FROM attendance a
JOIN trainee t ON a.trainee_id = t.trainee_id
LEFT JOIN trainee_allowance ta 
  ON t.trainee_id = ta.trainee_id 
  AND ta.month = ? 
  AND ta.year = ?
WHERE a.present = 1 AND a.date BETWEEN ? AND ?
GROUP BY t.trainee_id, t.full_name, ta.approval_status
HAVING jumlah_hadir >= 8
ORDER BY t.full_name
";

$stmt = $conn->prepare($sql);

// Debug jika prepare gagal
if (!$stmt) {
    die("Prepare gagal: " . $conn->error);
}

$stmt->bind_param("iiss", $bulan, $tahun, $tarikhMula, $tarikhAkhir);

// Debug jika execute gagal
if (!$stmt->execute()) {
    die("Query gagal: " . $stmt->error);
}

// Ambil hasil query
$result = $stmt->get_result();

// Elakkan warning: pastikan result tidak null sebelum digunakan
if (!$result) {
    die("Gagal dapatkan hasil: " . $stmt->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kehadiran Pelatih</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/allowance.css">
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

<!-- Main -->
 <div class="main-content">
    <?php if (isset($_SESSION['success'])): ?>
      <div style="background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 15px;">
        <?= $_SESSION['success'] ?>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
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

  <h2>PERTUBUHAN PEMULIHAN DALAM KOMUNITI INSANI</h2>
  <h3>SENARAI NAMA PELATIH BAGI PERMOHONAN ELAUN <?= namaBulanMelayu($bulan) ?> <?= $tahun ?></h3>
  </div>

  <table>
    <thead>
      <tr>
        <th>Bil</th>
        <th>Nama Pelatih</th>
        <th>Jumlah Kehadiran</th>
        <th>Kelulusan Penyelia</th>
        <th>Status Kelulusan</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($result->num_rows > 0): $bil = 1; ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $bil++ ?></td>
          <td><?= htmlspecialchars($row['full_name']) ?></td>
          <td><?= $row['jumlah_hadir'] ?></td>
          <td>
            <form method="post" action="svtrainee_allowance_process.php" style="margin: 0; display: flex; gap: 5px;">
              <input type="hidden" name="trainee_id" value="<?= $row['trainee_id'] ?>">
              <input type="hidden" name="bulan" value="<?= $bulan ?>">
              <input type="hidden" name="tahun" value="<?= $tahun ?>">
              <select name="approval_status" required>
                <option value="">-- Pilih --</option>
                <option value="Diluluskan" <?= $row['approval_status'] == 'Diluluskan' ? 'selected' : '' ?>>Diluluskan</option>
                <option value="Ditolak" <?= $row['approval_status'] == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
              </select>
              <button type="submit" name="kemaskini_status">Simpan</button>
            </form>
          </td>

          <td>
            <?php
              if ($row['approval_status'] === 'Diluluskan') {
                echo 'Diluluskan';
              } elseif ($row['approval_status'] === 'Ditolak') {
                echo 'Ditolak';
              } elseif (!is_null($row['approval_status'])) {
                // Dah tekan mohon, tapi belum diluluskan atau ditolak
                echo 'Menunggu Kelulusan';
              } else {
                // Belum tekan mohon langsung
                echo '';
              }
            ?>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="5">Tiada pelatih layak untuk memohon elaun bulan ini.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
    <p style="color: red; font-weight: bold; text-align: center; margin-left: 100px;">*Hanya pelatih yang mempunyai sekurang-kurangnya 8 hari kehadiran dalam bulan ini layak untuk memohon elaun.</p>
<br>


  </div>
</div>


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

