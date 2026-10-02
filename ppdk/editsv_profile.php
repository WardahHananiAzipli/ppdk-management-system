<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ppdk_db');
$username = $_SESSION['username'];

// Panggil data pengguna DULU
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];

    if (!empty($_FILES['profile_pic']['name'])) {
        // Padam gambar lama jika wujud dan bukan gambar default
        if (!empty($data['profile_pic']) && file_exists("uploads/" . $data['profile_pic'])) {
            unlink("uploads/" . $data['profile_pic']);
        }

        // Generate nama unik untuk gambar baru
        $originalName = pathinfo($_FILES["profile_pic"]["name"], PATHINFO_FILENAME);
        $extension = pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION);
        $uniqueName = $originalName . '_' . time() . '.' . $extension;
        $targetFilePath = "uploads/" . $uniqueName;

        // Pindahkan fail ke folder upload
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFilePath);

        // Kemas kini rekod pengguna dengan gambar baru
        $updateQuery = "UPDATE users SET name=?, email=?, phone=?, profile_pic=? WHERE username=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssss", $name, $email, $phone, $uniqueName, $username);
    } else {
        $updateQuery = "UPDATE users SET name=?, email=?, phone=? WHERE username=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssss", $name, $email, $phone, $username);
    }

    $stmt->execute();
    header("Location: sv_index.php?update=success");
    exit;
}

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
?>

<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <title>Edit Profil</title>
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

<!-- Main Body -->
<main class="profile-section">
  <form action="" method="post" enctype="multipart/form-data" class="profile-edit-form">
    <div class="profile-image profile-picture-center">
    <?php if (!empty($data['profile_pic'])): ?>
        <img src="<?= htmlspecialchars($profilePic) ?>" alt="Gambar Profil" class="photo-img-center">
    <?php else: ?>
        <div class="photo-img-center icon-fallback">
        <i class="fas fa-user"></i>
        </div>
    <?php endif; ?>
    </div>

    <div class="upload-wrapper">
    <label for="profile_pic" class="upload-label">Pilih Gambar</label>
    <input type="file" id="profile_pic" name="profile_pic" accept="image/*" class="upload-box">
    </div>

    <div class="profile-info">
      <h2><?= htmlspecialchars($data['name']) ?></h2>
      <p class="profile-role"><i class="fas fa-user-tag"></i> <?= $roleMalay[$data['role']] ?? ucfirst($data['role']) ?></p>
      <table class="form-table">
        <tr>
          <td><i class="fas fa-user"></i> Nama Penuh</td>
          <td>: <input type="text" name="name" value="<?= htmlspecialchars($data['name']) ?>"></td>
        </tr>
        <tr>
          <td><i class="fas fa-envelope"></i> Alamat E-mail</td>
          <td>: <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>"></td>
        </tr>
        <tr>
          <td><i class="fas fa-phone"></i> No. Telefon Bimbit</td>
          <td>: <input type="text" name="phone" value="<?= htmlspecialchars($data['phone']) ?>"></td>
        </tr>
        <tr>
          <td><i class="fas fa-user"></i> ID Pengguna</td>
          <td>: <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>"></td>
        </tr>
        <tr>
          <td><i class="fas fa-key"></i> Kata Laluan</td>
          <td>:
            <div class="password-field">
            <input type="password" name="password" id="password" value="<?= htmlspecialchars($data['password'] ?? '') ?>">
            <i id="togglePassword" class="fas fa-eye-slash toggle-password" onclick="togglePassword()"></i>
            </div>
          </td>
        </tr>
        <tr>
          <td><i class="fas fa-clock"></i> Log Masuk Terakhir</td>
          <td>: <?= !empty($data['last_login']) ? date('d/m/Y h:i A', strtotime($data['last_login'])) : 'Tiada rekod' ?></td>
        </tr>
      </table>

      <div class="form-button">
        <button type="submit" class="btn-edit">Simpan</button>
        <a href="sv_index.php" class="btn-back">Kembali</a>
      </div>
    </div>
  </form>
</main>

<!-- Password hide -->
<script src="js/password.js"></script>

<!-- Scroll button -->
<button id="scrollToTop" class="scroll-to-top"><i class="fas fa-arrow-up"></i></button>
<script src="js/scroll.js"></script>

<!-- Footer -->
<footer>
  <p>&copy; 2025 Pertubuhan Pemulihan Dalam Komuniti Insani Machang</p>
  <div class="social-icons">
    <a href="https://www.facebook.com/ppdkinsanikgrajamachang" target="_blank"><i class="fab fa-facebook-f"></i></a>
    <a href="https://www.instagram.com/ppdki" target="_blank"><i class="fab fa-instagram"></i></a>
    <a href="https://www.tiktok.com/@ppdkinsani" target="_blank"><i class="fab fa-tiktok"></i></a>
  </div>
</footer>
</body>
</html>
