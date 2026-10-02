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

$trainee_id = $_GET['id'] ?? '';

$traineeOptions = [];
// Jika ada bulan & tahun, tapis ikut tarikh daftar
if (isset($_GET['month']) && isset($_GET['year'])) {
    $stmt = $conn->prepare("SELECT trainee_id, full_name FROM trainee WHERE MONTH(registration_date) = ? AND YEAR(registration_date) = ?");
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $traineeOptions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    // Kalau belum tekan filter, ambil semua trainee
    $stmt = $conn->prepare("SELECT trainee_id, full_name FROM trainee");
    $stmt->execute();
    $traineeOptions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Dapatkan maklumat trainee hanya jika trainee_id dihantar
$traineeData = null;

if (!empty($trainee_id)) {
    $stmt = $conn->prepare("SELECT * FROM trainee WHERE trainee_id = ?");
    $stmt->bind_param("i", $trainee_id);
    $stmt->execute();
    $traineeData = $stmt->get_result()->fetch_assoc();
}

// Function bantu
function is_checked($value, $data) {
    $items = json_decode($data, true);
    if (!is_array($items)) {
        $items = explode(",", $data);
    }
    return in_array(trim($value), array_map('trim', $items)) ? "checked" : "";
}

function show_value($field) {
    return htmlspecialchars($field ?? '');
}

function show_field($value, $suffix = '', $prefix = '', $default = '') {
    if (!empty($value)) {
        return $prefix . htmlspecialchars($value) . $suffix;
    }
    return $default;
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <title>Lihat Pendaftaran Pelatih</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/registration.css">
  <link rel="stylesheet" href="css/scroll.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    @media print {
      .page-break { page-break-before: always; }
      .form-button, header, footer, .sidebar { display: none; }
      body, main { margin: 0; padding: 0; }
    }
    input[disabled] {
      background-color: transparent;
      border: none;
      color: #000;
      pointer-events: none;
    }
    .form-table input[type="checkbox"],
    .form-table input[type="radio"] {
      transform: scale(1.1);
    }
  </style>
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

  <!-- Satu Pelatih -->
  <div class="form-container" style="margin: 0 auto; width: 50%;">
  <?php if (!empty($traineeOptions)): ?>
    <form method="GET">
      <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">
      <input type="hidden" name="year" value="<?= htmlspecialchars($year) ?>">

      <label for="trainee_id">Nama Pelatih:</label>
      <select name="trainee_id" id="trainee_id" required>
        <option value="" disabled <?= empty($trainee_id) ? 'selected' : '' ?>>---- Pilih Pelatih ----</option>
        <?php foreach ($traineeOptions as $s): ?>
          <option value="<?= $s['trainee_id']; ?>" <?= ($s['trainee_id'] == $trainee_id) ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['full_name']) ?>
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
<?php if (!empty($trainee_id) && $traineeData): ?>
  <div class="form-header">
    <div class="form-header-top">
    <div class="form-code-left">NO KOD :</div>
    <div class="form-code-right">BORANG  PDK 1 / LAMPIRAN 1</div>
  </div>

<!-- SECTION A -->
  <div class="form-title">
    <h2>BORANG PENDAFTARAN PELATIH</h2>
    <p style="text-align: left;"><strong>NEGERI : KELANTAN</strong></p>
    <h4 style="text-align: left;">A. BUTIRAN PERIBADI</h4>
  </div>

<table class="form-table">

<tr>
  <td>1. Nama</td><td>: <?= show_field($trainee['full_name']) ?></td>
  <td rowspan="12" style="border: none;">
    <?php if (!empty($trainee['profile_pic'])): ?>
      <img src="<?= htmlspecialchars($trainee['profile_pic']) ?>" width="120" height="150" style="border: 1px solid black;">
    <?php else: ?>
      <img src="img/profile-placeholder.jpg" width="120" height="150">
    <?php endif; ?>
  </td>
</tr>
<tr><td>2. No Kad Pengenalan</td><td>: <?= show_field($trainee['ic']) ?></td></tr>
<tr><td>3. No. Pendaftaran OKU</td><td>: <?= show_field($trainee['oku_num']) ?></td></tr>
<tr><td>4. Tarikh Lahir / Umur</td><td>: <?= show_field($trainee['dob']) ?> / <?= show_field($trainee['age']) ?></td></tr>
<tr><td>5. Jantina</td><td>: <?= show_field($trainee['gender']) ?></td></tr>
<tr><td>6. Etnik / Bangsa</td><td>: <?= show_field($trainee['race']) ?></td></tr>
<tr><td>7. Agama</td><td>: <?= show_field($trainee['religion']) ?></td></tr>
<tr><td>8. Bilangan adik-beradik</td><td>: <?= show_field($trainee['num_siblings']) ?> orang</td></tr>
<tr><td>9. Bilangan dalam keluarga</td><td>: Anak ke<?= show_field($trainee['family_position']) ?></td></tr>
<tr><td>10. Alamat</td><td>: <?= show_field($trainee['address']) ?></td></tr>
<tr><td>11. No. Telefon Rumah</td><td>: <?= show_field($trainee['home_phone']) ?></td></tr>
<tr><td>12. No. Tel Bimbit</td><td>: <?= show_field($trainee['mobile_phone']) ?></td></tr>
    
<tr><td colspan="2">
13. Boleh datang sendiri ke PDK?<br>
<input type="radio" <?= $trainee['can_come_alone'] == 'Tidak' ? 'checked' : '' ?> disabled> Tidak
<input type="radio" <?= $trainee['can_come_alone'] == 'Ya' ? 'checked' : '' ?> disabled> Ya
(Sila nyatakan): <input type="text" value="<?= show_value($trainee['can_come_alone_note']) ?>" disabled>
</td></tr>

<tr><td colspan="2">
14. Jika tidak, dihantar oleh keluarga menggunakan:<br>
<?php
$methods = ['Berjalan', 'Berbasikal', 'Motosikal', 'Kereta', 'Kenderaan Awam', 'Lain-lain'];
foreach ($methods as $method): ?>
  <label><input type="checkbox" <?= is_checked($method, $trainee['transport_method']) ?> disabled> <?= $method ?></label><br>
<?php endforeach; ?>
</td></tr>

<tr><td colspan="2">
15. Lawatan rumah dan ekonomi:<br>
<input type="radio" <?= $trainee['jkm_referral'] == 'Tidak' ? 'checked' : '' ?> disabled> Tidak
<input type="radio" <?= $trainee['jkm_referral'] == 'Ya' ? 'checked' : '' ?> disabled> Ya
(Sebutkan): <input type="text" value="<?= show_value($trainee['jkm_reason']) ?>" disabled>
</td></tr>
</table>

<!-- SECTION B -->
<div class="page-break"></div> 
<h3 style="margin-top: 40px;">B. BUTIRAN IBU BAPA / PENJAGA</h3>
<table class="program-table">
  <thead>
    <tr>
      <th>BIL</th>
      <th>BUTIRAN</th>
      <th>BAPA / PENJAGA</th>
      <th>IBU / PENJAGA</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>1</td><td>Nama</td>
      <td><?= show_field($trainee['father_name']) ?></td>
      <td><?= show_field($trainee['mother_name']) ?></td>
    </tr>
    <tr><td>2</td><td>No. Kad Pengenalan</td>
      <td><?= show_field($trainee['father_ic']) ?></td>
      <td><?= show_field($trainee['mother_ic']) ?></td>
    </tr>
    <tr><td>3</td><td>Umur</td>
      <td><?= show_field($trainee['father_age']) ?></td>
      <td><?= show_field($trainee['mother_age']) ?></td>
    </tr>
    <tr><td>4</td><td>Pekerjaan</td>
      <td><?= show_field($trainee['father_job']) ?></td>
      <td><?= show_field($trainee['mother_job']) ?></td>
    </tr>
    <tr><td>5</td><td>Pendapatan</td>
      <td><?= show_field($trainee['father_income']) ?></td>
      <td><?= show_field($trainee['mother_income']) ?></td>
    </tr>
    <tr><td>6</td><td>Jenis Ketidakupayaan (Jika Ada)</td>
      <td><?= show_field($trainee['father_disability']) ?></td>
      <td><?= show_field($trainee['mother_disability']) ?></td>
    </tr>
    <tr><td>7</td><td>Penerima Bantuan (Ya/Tidak)</td>
      <td><?= show_field($trainee['father_assistance']) ?></td>
      <td><?= show_field($trainee['mother_assistance']) ?></td>
    </tr>
    <tr><td>8</td><td>Jika Ya, Nyatakan:
        <br>a. Jenis
        <br>b. Kadar Bantuan
        <br>c. Agensi/Jabatan
    </td>
      <td>
        <br>
        <?= show_field($trainee['father_aid_type']) ?><br>
        <?= show_field($trainee['father_aid_amount']) ?><br>
        <?= show_field($trainee['father_agency']) ?>
      </td>
      <td>
        <br>
        <?= show_field($trainee['mother_aid_type']) ?><br>
        <?= show_field($trainee['mother_aid_amount']) ?><br>
        <?= show_field($trainee['mother_agency']) ?>
      </td>
    </tr>
  </tbody>
</table>

<!-- SECTION C -->
<div class="page-break"></div> 
<h3 style="margin-top: 40px;">C. TAHAP KEUPAYAAN PELATIH</h3>
<table class="form-table">
  <tr><td colspan="2">1. Kategori OKU:</td></tr>
  <tr><td>(a) Tahap OKU:</td>
    <td>
      <label><input type="radio" <?= $trainee['disability_level'] == 'Ringan' ? 'checked' : '' ?> disabled> Ringan</label>
      <label><input type="radio" <?= $trainee['disability_level'] == 'Sederhana' ? 'checked' : '' ?> disabled> Sederhana</label>
      <label><input type="radio" <?= $trainee['disability_level'] == 'Teruk' ? 'checked' : '' ?> disabled> Teruk</label>
    </td>
  </tr>
  <tr><td>(b) Perlu alat bantuan khas?</td>
    <td>
      <label><input type="radio" <?= $trainee['need_assistive'] == 'Ya' ? 'checked' : '' ?> disabled> Ya</label>
      <label><input type="radio" <?= $trainee['need_assistive'] == 'Tidak' ? 'checked' : '' ?> disabled> Tidak</label>
      <br>Jika Ya, nyatakan:
      <input type="text" value="<?= show_value($trainee['assistive_tool']) ?>" disabled>
    </td>
  </tr>

  <tr>
    <td>2. Jenis penyakit (jika ada):</td>
    <td><input type="text" value="<?= show_value($trainee['illness_type']) ?>" disabled></td>
  </tr>

 <tr>
    <td colspan="2">3. Sikap pelatih di dalam keluarga:<br><br>
      <div style="display: flex; flex-wrap: wrap; gap: 10px; max-width: 500px;">
        <?php
          $behaviours = ['Pendiam', 'Suka Bergaul', 'Suka Bergaduh', 'Menyendiri', 'Agresif', 'Melawan'];
          $behaviour_data = [];

            if (!empty($trainee['behaviour'])) {
                $decoded = json_decode($trainee['behaviour'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $behaviour_data = $decoded;
                } else {
                    // fallback kalau bukan JSON, anggap comma-separated string
                    $behaviour_data = array_map('trim', explode(',', $trainee['behaviour']));
                }
            }

          $behaviour_other = '';

          foreach ($behaviour_data as $b) {
              if (!in_array($b, $behaviours) && $b !== 'Lain-lain') {
                  $behaviour_other = $b; // simpan sebagai "lain-lain"
              }
          }

          foreach ($behaviours as $item): ?>
            <label style="width: 48%; display: flex; align-items: center;">
              <input type="checkbox" <?= in_array($item, $behaviour_data) ? 'checked' : '' ?> disabled> &nbsp;<?= $item ?>
            </label>
        <?php endforeach; ?>
      </div>

      <!-- Lain-lain: satu baris penuh -->
      <div style="margin-top: 10px;">
        <label style="display: flex; align-items: center; gap: 10px;">
          <input type="checkbox" <?= in_array('Lain-lain', $behaviour_data) ? 'checked' : '' ?> disabled>
          <span>Lain-lain:</span>
          <input type="text" value="<?= htmlspecialchars($behaviour_other) ?>" disabled style="flex: 1; min-width: 200px;">
        </label>
      </div>
    </td>
  </tr>


  <tr>
    <td colspan="2">4. Kebolehan menguruskan diri:<br><br>
        <div style="display: flex; flex-wrap: wrap; gap: 10px; max-width: 500px;">
        <?php
        $self_care = ['Makan dan minum sendiri', 'Menggosok gigi', 'Pergi ke tandas', 'Mencuci tangan', 'Memakai baju'];
        foreach ($self_care as $item): ?>
            <label style="width: 48%;"><input type="checkbox" <?= is_checked($item, $trainee['self_care']) ?> disabled> <?= $item ?></label>
        <?php endforeach; ?>
        </div>
    </td>
  </tr>

  <tr>
    <td colspan="2">5. Kebolehan bergerak:<br><br>
        <div style="display: flex; flex-wrap: wrap; gap: 10px; max-width: 500px;">
        <?php
        $mobility = ['Berlari', 'Merangkak', 'Berjalan', 'Meniarap', 'Melompat', 'Baring'];
        foreach ($mobility as $item): ?>
            <label style="width: 48%;"><input type="checkbox" <?= is_checked($item, $trainee['mobility']) ?> disabled> <?= $item ?></label>
        <?php endforeach; ?>
        </div>
    </td>
  </tr>

</table>

  <div class="form-button">  
    <button onclick="window.print()" class="btn-print">Cetak Borang</button>
  </div>

<?php endif; ?>

</div>

<!-- Semua Pelatih -->
<?php if (empty($trainee_id) && !empty($traineeOptions)): ?>
    <?php foreach ($traineeOptions as $s): ?>
        <?php
        $stmt = $conn->prepare("SELECT * FROM trainee WHERE trainee_id = ?");
        $stmt->bind_param("i", $s['trainee_id']);
        $stmt->execute();
        $trainee = $stmt->get_result()->fetch_assoc();
        ?>

    <div class="form-header page-break">
    <div class="form-header">
        <div class="form-header-top">
        <div class="form-code-left">NO KOD :</div>
        <div class="form-code-right">BORANG  PDK 1 / LAMPIRAN 1</div>
    </div>

    <!-- SECTION A -->
    <div class="form-title">
        <h2>BORANG PENDAFTARAN PELATIH</h2>
        <p style="text-align: left;"><strong>NEGERI : KELANTAN</strong></p>
        <h4 style="text-align: left;">A. BUTIRAN PERIBADI</h4>
    </div>

    <table class="form-table">

    <tr>
    <td>1. Nama</td><td>: <?= show_field($trainee['full_name']) ?></td>
    <td rowspan="12" style="border: none;">
        <?php if (!empty($trainee['profile_pic'])): ?>
        <img src="<?= htmlspecialchars($trainee['profile_pic']) ?>" width="120" height="150" style="border: 1px solid black;">
        <?php else: ?>
        <img src="img/profile-placeholder.jpg" width="120" height="150">
        <?php endif; ?>
    </td>
    </tr>
    <tr><td>2. No Kad Pengenalan</td><td>: <?= show_field($trainee['ic']) ?></td></tr>
    <tr><td>3. No. Pendaftaran OKU</td><td>: <?= show_field($trainee['oku_num']) ?></td></tr>
    <tr><td>4. Tarikh Lahir / Umur</td><td>: <?= show_field($trainee['dob']) ?> / <?= show_field($trainee['age']) ?></td></tr>
    <tr><td>5. Jantina</td><td>: <?= show_field($trainee['gender']) ?></td></tr>
    <tr><td>6. Etnik / Bangsa</td><td>: <?= show_field($trainee['race']) ?></td></tr>
    <tr><td>7. Agama</td><td>: <?= show_field($trainee['religion']) ?></td></tr>
    <tr><td>8. Bilangan adik-beradik</td><td>: <?= show_field($trainee['num_siblings']) ?> orang</td></tr>
    <tr><td>9. Bilangan dalam keluarga</td><td>: Anak ke<?= show_field($trainee['family_position']) ?></td></tr>
    <tr><td>10. Alamat</td><td>: <?= show_field($trainee['address']) ?></td></tr>
    <tr><td>11. No. Telefon Rumah</td><td>: <?= show_field($trainee['home_phone']) ?></td></tr>
    <tr><td>12. No. Tel Bimbit</td><td>: <?= show_field($trainee['mobile_phone']) ?></td></tr>
        
    <tr><td colspan="2">
    13. Boleh datang sendiri ke PDK?<br>
    <input type="radio" <?= $trainee['can_come_alone'] == 'Tidak' ? 'checked' : '' ?> disabled> Tidak
    <input type="radio" <?= $trainee['can_come_alone'] == 'Ya' ? 'checked' : '' ?> disabled> Ya
    (Sila nyatakan): <input type="text" value="<?= show_value($trainee['can_come_alone_note']) ?>" disabled>
    </td></tr>

    <tr><td colspan="2">
    14. Jika tidak, dihantar oleh keluarga menggunakan:<br>
    <?php
    $methods = ['Berjalan', 'Berbasikal', 'Motosikal', 'Kereta', 'Kenderaan Awam', 'Lain-lain'];
    foreach ($methods as $method): ?>
    <label><input type="checkbox" <?= is_checked($method, $trainee['transport_method']) ?> disabled> <?= $method ?></label><br>
    <?php endforeach; ?>
    </td></tr>

    <tr><td colspan="2">
    15. Lawatan rumah dan ekonomi:<br>
    <input type="radio" <?= $trainee['jkm_referral'] == 'Tidak' ? 'checked' : '' ?> disabled> Tidak
    <input type="radio" <?= $trainee['jkm_referral'] == 'Ya' ? 'checked' : '' ?> disabled> Ya
    (Sebutkan): <input type="text" value="<?= show_value($trainee['jkm_reason']) ?>" disabled>
    </td></tr>
    </table>

    <!-- SECTION B -->
    <div class="page-break"></div> 
    <h3 style="margin-top: 40px;">B. BUTIRAN IBU BAPA / PENJAGA</h3>
    <table class="program-table">
    <thead>
        <tr>
        <th>BIL</th>
        <th>BUTIRAN</th>
        <th>BAPA / PENJAGA</th>
        <th>IBU / PENJAGA</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>1</td><td>Nama</td>
        <td><?= show_field($trainee['father_name']) ?></td>
        <td><?= show_field($trainee['mother_name']) ?></td>
        </tr>
        <tr><td>2</td><td>No. Kad Pengenalan</td>
        <td><?= show_field($trainee['father_ic']) ?></td>
        <td><?= show_field($trainee['mother_ic']) ?></td>
        </tr>
        <tr><td>3</td><td>Umur</td>
        <td><?= show_field($trainee['father_age']) ?></td>
        <td><?= show_field($trainee['mother_age']) ?></td>
        </tr>
        <tr><td>4</td><td>Pekerjaan</td>
        <td><?= show_field($trainee['father_job']) ?></td>
        <td><?= show_field($trainee['mother_job']) ?></td>
        </tr>
        <tr><td>5</td><td>Pendapatan</td>
        <td><?= show_field($trainee['father_income']) ?></td>
        <td><?= show_field($trainee['mother_income']) ?></td>
        </tr>
        <tr><td>6</td><td>Jenis Ketidakupayaan (Jika Ada)</td>
        <td><?= show_field($trainee['father_disability']) ?></td>
        <td><?= show_field($trainee['mother_disability']) ?></td>
        </tr>
        <tr><td>7</td><td>Penerima Bantuan (Ya/Tidak)</td>
        <td><?= show_field($trainee['father_assistance']) ?></td>
        <td><?= show_field($trainee['mother_assistance']) ?></td>
        </tr>
        <tr><td>8</td><td>Jika Ya, Nyatakan:
            <br>a. Jenis
            <br>b. Kadar Bantuan
            <br>c. Agensi/Jabatan
        </td>
        <td>
            <br>
            <?= show_field($trainee['father_aid_type']) ?><br>
            <?= show_field($trainee['father_aid_amount']) ?><br>
            <?= show_field($trainee['father_agency']) ?>
        </td>
        <td>
            <br>
            <?= show_field($trainee['mother_aid_type']) ?><br>
            <?= show_field($trainee['mother_aid_amount']) ?><br>
            <?= show_field($trainee['mother_agency']) ?>
        </td>
        </tr>
    </tbody>
    </table>

    <!-- SECTION C -->
    <div class="page-break"></div> 
    <h3 style="margin-top: 40px;">C. TAHAP KEUPAYAAN PELATIH</h3>
    <table class="form-table">
    <tr><td colspan="2">1. Kategori OKU:</td></tr>
    <tr><td>(a) Tahap OKU:</td>
        <td>
        <label><input type="radio" <?= $trainee['disability_level'] == 'Ringan' ? 'checked' : '' ?> disabled> Ringan</label>
        <label><input type="radio" <?= $trainee['disability_level'] == 'Sederhana' ? 'checked' : '' ?> disabled> Sederhana</label>
        <label><input type="radio" <?= $trainee['disability_level'] == 'Teruk' ? 'checked' : '' ?> disabled> Teruk</label>
        </td>
    </tr>
    <tr><td>(b) Perlu alat bantuan khas?</td>
        <td>
        <label><input type="radio" <?= $trainee['need_assistive'] == 'Ya' ? 'checked' : '' ?> disabled> Ya</label>
        <label><input type="radio" <?= $trainee['need_assistive'] == 'Tidak' ? 'checked' : '' ?> disabled> Tidak</label>
        <br>Jika Ya, nyatakan:
        <input type="text" value="<?= show_value($trainee['assistive_tool']) ?>" disabled>
        </td>
    </tr>

    <tr>
        <td>2. Jenis penyakit (jika ada):</td>
        <td><input type="text" value="<?= show_value($trainee['illness_type']) ?>" disabled></td>
    </tr>

    <tr>
        <td colspan="2">3. Sikap pelatih di dalam keluarga:<br><br>
        <div style="display: flex; flex-wrap: wrap; gap: 10px; max-width: 500px;">
            <?php
            $behaviours = ['Pendiam', 'Suka Bergaul', 'Suka Bergaduh', 'Menyendiri', 'Agresif', 'Melawan'];
            $behaviour_data = [];

                if (!empty($trainee['behaviour'])) {
                    $decoded = json_decode($trainee['behaviour'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $behaviour_data = $decoded;
                    } else {
                        // fallback kalau bukan JSON, anggap comma-separated string
                        $behaviour_data = array_map('trim', explode(',', $trainee['behaviour']));
                    }
                }

            $behaviour_other = '';

            foreach ($behaviour_data as $b) {
                if (!in_array($b, $behaviours) && $b !== 'Lain-lain') {
                    $behaviour_other = $b; // simpan sebagai "lain-lain"
                }
            }

            foreach ($behaviours as $item): ?>
                <label style="width: 48%; display: flex; align-items: center;">
                <input type="checkbox" <?= in_array($item, $behaviour_data) ? 'checked' : '' ?> disabled> &nbsp;<?= $item ?>
                </label>
            <?php endforeach; ?>
        </div>

        <!-- Lain-lain: satu baris penuh -->
        <div style="margin-top: 10px;">
            <label style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" <?= in_array('Lain-lain', $behaviour_data) ? 'checked' : '' ?> disabled>
            <span>Lain-lain:</span>
            <input type="text" value="<?= htmlspecialchars($behaviour_other) ?>" disabled style="flex: 1; min-width: 200px;">
            </label>
        </div>
        </td>
    </tr>


    <tr>
        <td colspan="2">4. Kebolehan menguruskan diri:<br><br>
            <div style="display: flex; flex-wrap: wrap; gap: 10px; max-width: 500px;">
            <?php
            $self_care = ['Makan dan minum sendiri', 'Menggosok gigi', 'Pergi ke tandas', 'Mencuci tangan', 'Memakai baju'];
            foreach ($self_care as $item): ?>
                <label style="width: 48%;"><input type="checkbox" <?= is_checked($item, $trainee['self_care']) ?> disabled> <?= $item ?></label>
            <?php endforeach; ?>
            </div>
        </td>
    </tr>

    <tr>
        <td colspan="2">5. Kebolehan bergerak:<br><br>
            <div style="display: flex; flex-wrap: wrap; gap: 10px; max-width: 500px;">
            <?php
            $mobility = ['Berlari', 'Merangkak', 'Berjalan', 'Meniarap', 'Melompat', 'Baring'];
            foreach ($mobility as $item): ?>
                <label style="width: 48%;"><input type="checkbox" <?= is_checked($item, $trainee['mobility']) ?> disabled> <?= $item ?></label>
            <?php endforeach; ?>
            </div>
        </td>
    </tr>
    </table>   
    </div>
      <?php endforeach; ?>

    <?php elseif (!empty($traineeOptions)): ?>
      <p><em>Sila pilih pelatih dan tekan butang <strong>"Pilih"</strong> untuk paparkan maklumat.</em></p>

    <?php else: ?>
      <p><em>Tiada pelatih didaftarkan bagi bulan dan tahun yang dipilih.</em></p>
    <?php endif; ?>

  </main>

<!-- Scroll button -->
<button id="scrollToTop" class="scroll-to-top"><i class="fas fa-arrow-up"></i></button>
<script src="js/scroll.js"></script>

<!-- Footer -->
<footer>
  <p>&copy; 2025 Pertubuhan Pemulihan Dalam Komuniti Insani Machang</p>
  <div class="social-icons">
    <a href="https://www.facebook.com/ppdkinsanikgrajamachang?mibextid=ZbWKwL" target="_blank"><i class="fab fa-facebook-f"></i></a>
    <a href="https://www.instagram.com/ppdki?igsh=Zm4waDU2Z2Q0b3gw" target="_blank"><i class="fab fa-instagram"></i></a>
    <a href="https://www.tiktok.com/@ppdkinsani?_t=ZS-8xJNXvkebHU&_r=1" target="_blank"><i class="fab fa-tiktok"></i></a>
  </div>
</footer>

</body>
</html>
