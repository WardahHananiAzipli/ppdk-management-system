<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ppdk_db');
$username = $_SESSION['username'];
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$roleMalay = [
  'instructor' => 'Petugas',
  'supervisor' => 'Penyelia',
  'chairman'   => 'Pengerusi'
];

$profilePic = !empty($data['profile_pic']) ? 'uploads/' . $data['profile_pic'] : 'img/profile-placeholder.jpg';
$updateSuccess = isset($_GET['update']) && $_GET['update'] === 'success';
?>

<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Pengguna</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/profile.css">
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

<!-- Main Body -->
<main class="profile-section">
  <?php if ($updateSuccess): ?>
    <div class="alert-success">Profil berjaya dikemaskini.</div>
  <?php endif; ?>

    <div class="profile-image profile-picture-center">
      <?php if (!empty($data['profile_pic'])): ?>
        <img src="<?= htmlspecialchars($profilePic) ?>" alt="Gambar Profil" class="photo-img-center">
      <?php else: ?>
        <div class="photo-img-center icon-fallback">
          <i class="fas fa-user"></i>
        </div>
      <?php endif; ?>
    </div>

    <div class="profile-info">
      <h2><?= htmlspecialchars($data['name']) ?></h2>
      <p class="profile-role"><i class="fas fa-user-tag"></i> <?= $roleMalay[$data['role']] ?? ucfirst($data['role']) ?></p>
      <table class="form-table">
        <tr><td><i class="fas fa-envelope"></i> Alamat E-mail</td><td>: <?= htmlspecialchars($data['email']) ?></td></tr>
        <tr><td><i class="fas fa-phone"></i> No. Telefon Bimbit</td><td>: <?= htmlspecialchars($data['phone']) ?></td></tr>
        <tr><td><i class="fas fa-user"></i> ID Pengguna</td><td>: <?= htmlspecialchars($data['username']) ?></td></tr>
        <tr><td><i class="fas fa-key"></i> Kata Laluan</td><td>: ********</td></tr>
        <tr><td><i class="fas fa-clock"></i> Log Masuk Terakhir</td><td>: <?= !empty($data['last_login']) ? date('d/m/Y h:i A', strtotime($data['last_login'])) : 'Tiada rekod' ?></td></tr>
      </table>

      <div class="form-button">
        <a href="edit_profile.php" class="btn-edit">Kemaskini Profil</a>
      </div>
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
    <a href="https://www.facebook.com/ppdkinsanikgrajamachang" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
    <a href="https://www.instagram.com/ppdki" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
    <a href="https://www.tiktok.com/@ppdkinsani" target="_blank" title="TikTok"><i class="fab fa-tiktok"></i></a>
  </div>
</footer>
</body>
</html>
