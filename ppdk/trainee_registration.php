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
?>

<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pendaftaran Pelatih</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/registration.css">
  <link rel="stylesheet" href="css/scroll.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>

      .step { display: none; }
      .step.active { display: block; }
      .step-nav button { margin: 5px; padding: 10px; }

      @media print {
        .step { display: block !important; page-break-after: always; }
        .step-nav { display: none !important; }
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

    <!-- Header -->
      <div class="form-header">
        <div class="form-header-top">
          <div class="form-code-left">NO KOD :</div>
          <div class="form-code-right">BORANG  PDK 1 / LAMPIRAN 1</div>
      </div>

      <form name="trainee_registration" method="post" action="trainee_registration_process.php" enctype="multipart/form-data">

      <!-- SECTION A -->
      <div id="stepA" class="step active">

      <div class="form-title">
        <h2>BORANG PENDAFTARAN PELATIH</h2>
        <p style="text-align: left;"><strong>NEGERI : KELANTAN</strong></p>
        <h4 style="text-align: left;">A. BUTIRAN PERIBADI</h4>
      </div>
      
        <table class="form-table">
          <tr>
            <td>1. Nama</td>
            <td>: <input type="text" name="full_name"></td>
            <td rowspan="12" class="photo" style="text-align: center;">
              <!-- Icon fallback -->
              <div class="photo-img icon-fallback">
                <i class="fas fa-user"></i>
              </div>

              <!-- Input gambar -->
              <input type="file" name="profile_pic" accept="image/*">
            </td>
          </tr>
          <tr>
            <td>2. No Kad Pengenalan / Sijil Kelahiran</td>
            <td>: <input type="text" name="ic"></td>
          </tr>
          <tr>
            <td>3. No. Pendaftaran OKU</td>
            <td>: <input type="text" name="oku_num"></td>
          </tr>
          <tr>
            <td>4. Tarikh Lahir / Umur</td>
            <td>: 
              <input type="date" name="dob"> / 
              <input type="number" name="age" min="0" style="width: 50px;"> tahun
            </td>
          </tr>
          <tr>
            <td>5. Jantina</td>
            <td>: 
              <select name="gender">
                <option value="">--Pilih--</option>
                <option value="Lelaki">Lelaki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </td>
          </tr>
          <tr>
            <td>6. Etnik / Bangsa</td>
            <td>: <input type="text" name="race"></td>
          </tr>
          <tr>
            <td>7. Agama</td>
            <td>: <input type="text" name="religion"></td>
          </tr>
          <tr>
            <td>8. Bilangan adik-beradik</td>
            <td>: <input type="number" name="num_siblings" min="0" style="width: 50px;"> orang</td>
          </tr>
          <tr>
            <td>9. Bilangan dalam keluarga</td>
            <td>: Anak ke <input type="number" name="family_position" min="0" style="width: 50px;"></td>
          </tr>
          <tr>
            <td>10. Alamat Tempat Tinggal</td>
            <td>: <textarea name="address" style="width: 95%;" rows="3"></textarea></td>
          </tr>
          <tr>
            <td>11. No. Telefon Rumah</td>
            <td>: <input type="text" name="home_phone"></td>
          </tr>
          <tr>
            <td>12. No. Tel Bimbit</td>
            <td>: <input type="text" name="mobile_phone"></td>
          </tr>

          <tr>
            <td colspan="2">
              13. Adakah pelatih boleh datang sendiri ke PDK?<br>
              <label><input type="radio" name="can_come_alone" value="Tidak"> Tidak</label>
              <label><input type="radio" name="can_come_alone" value="Ya"> Ya</label> 
              (Sila nyatakan): <input type="text" name="can_come_alone_note" style="width: 50%;">
            </td>
          </tr>

          <tr>
            <td colspan="2">
              14. Jika tidak, dihantar oleh keluarga menggunakan:<br>
              <div style="display: flex; flex-direction: column; gap: 4px; padding-top: 5px;">
                <?php
                  $transport_options = ['Berjalan', 'Berbasikal', 'Motosikal', 'Kereta', 'Kenderaan Awam', 'Lain-lain'];
                  foreach ($transport_options as $option):
                ?>
                <label>
                  <input type="checkbox" name="transport_method[]" value="<?= $option ?>"> <?= $option ?>
                </label>
                <?php endforeach; ?>
              </div>
            </td>
          </tr>

          <tr>
            <td colspan="2">
              15. Jika rumah pelatih sudah dilawati, adakah keadaan rumah dan ekonomi<br>keluarga terlalu daif dan memerlukan rujukan ke JKM?<br>
              <label><input type="radio" name="jkm_referral" value="Tidak"> Tidak</label>
              <label><input type="radio" name="jkm_referral" value="Ya"> Ya</label> 
              (Sebutkan keperluannya): <input type="text" name="jkm_reason" style="width: 40%;">
            </td>
          </tr>
        </table>

        <div class="form-button">
          <button type="button" class="btn-next-1" onclick="nextStep('stepB')">Seterusnya</button>
        </div>
      </div>

      <!-- SECTION B -->
      <div id="stepB" class="step">

      <div class="form-title">
        <h2>BORANG PENDAFTARAN PELATIH</h2>
        <p style="text-align: left;"><strong>NEGERI : KELANTAN</strong></p>
        <h4 style="text-align: left;">B. BUTIRAN IBU BAPA / PENJAGA</h4>
      </div>
      <br>
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
            <td><input type="text" name="father_name" /></td>
            <td><input type="text" name="mother_name" /></td>
          </tr>
          <tr><td>2</td><td>No. Kad Pengenalan</td>
            <td><input type="text" name="father_ic" /></td>
            <td><input type="text" name="mother_ic" /></td>
          </tr>
          <tr><td>3</td><td>Umur</td>
            <td><input type="number" name="father_age" /></td>
            <td><input type="number" name="mother_age" /></td>
          </tr>
          <tr><td>4</td><td>Pekerjaan</td>
            <td><input type="text" name="father_job" /></td>
            <td><input type="text" name="mother_job" /></td>
          </tr>
          <tr><td>5</td><td>Pendapatan</td>
            <td><input type="number" name="father_income" /></td>
            <td><input type="number" name="mother_income" /></td>
          </tr>
          <tr><td>6</td><td>Jenis Ketidakupayaan (Jika Ada)</td>
            <td><input type="text" name="father_disability" /></td>
            <td><input type="text" name="mother_disability" /></td>
          </tr>
          <tr><td>7</td><td>Penerima Bantuan (Ya/Tidak)</td>
            <td><input type="text" name="father_assistance" /></td>
            <td><input type="text" name="mother_assistance" /></td>
          </tr>
          <tr><td>8</td><td>
            Jika Ya, Nyatakan:
            <br>a. Jenis
            <br>b. Kadar Bantuan
            <br>c. Agensi/Jabatan
          </td>
            <td>
              <br>
              <input type="text" name="father_aid_type" placeholder="Jenis" /><br>
              <input type="text" name="father_aid_amount" placeholder="Kadar Bantuan" /><br>
              <input type="text" name="father_agency" placeholder="Agensi/Jabatan" />
            </td>
            <td>
              <br>
              <input type="text" name="mother_aid_type" placeholder="Jenis" /><br>
              <input type="text" name="mother_aid_amount" placeholder="Kadar Bantuan" /><br>
              <input type="text" name="mother_agency" placeholder="Agensi/Jabatan" />
            </td>
          </tr>
        </tbody>
      </table>

      <div class="form-button">
        <button type="button" class="btn-prev" onclick="showStep('stepA')">Sebelumnya</button>
        <button type="button" class="btn-next" onclick="nextStep('stepC')">Seterusnya</button>
      </div>

      </div>

      <!-- SECTION C -->
      <div id="stepC" class="step">

      <div class="form-title">
        <h2>BORANG PENDAFTARAN PELATIH</h2>
        <p style="text-align: left;"><strong>NEGERI : KELANTAN</strong></p>
        <h4 style="text-align: left;">C. TAHAP KEUPAYAAN PELATIH</h4>
      </div>
      <br>
      <table class="form-table">
          <tr><td colspan="2">1. Kategori OKU:</td></tr>

          <tr><td>(a) Tahap OKU:</td>
            <td>
              <label><input type="radio" name="disability_level" value="Ringan"> Ringan</label>
              <label><input type="radio" name="disability_level" value="Sederhana"> Sederhana</label>
              <label><input type="radio" name="disability_level" value="Teruk"> Teruk</label>
            </td>
          </tr>

        <tr>
          <td>(b) Adakah perlu alat bantuan khas?</td>
          <td>
            <label><input type="radio" name="need_assistive" value="Ya"> Ya</label>
            <label><input type="radio" name="need_assistive" value="Tidak"> Tidak</label>
            <br>Jika Ya, nyatakan:
            <input type="text" name="assistive_tool" placeholder="Alat bantuan khas" />
          </td>
        </tr>

        <tr>
          <td>2. Jenis penyakit (jika ada):</td>
          <td><input type="text" name="illness_type" /></td>
        </tr>

        <tr>
          <td colspan="2">
            3. Sikap pelatih di dalam keluarga:<br>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px 10px; padding-top: 5px;">
              <?php
                $behaviour_options = ['Pendiam', 'Suka Bergaul', 'Suka Bergaduh', 'Menyendiri', 'Agresif', 'Melawan'];
                foreach ($behaviour_options as $option):
              ?>
              <label>
                <input type="checkbox" name="behaviour[]" value="<?= $option ?>"> <?= $option ?>
              </label>
              <?php endforeach; ?>

              <!-- Lain-lain with text input, merged into full row -->
              <div style="grid-column: span 2; display: flex; align-items: center; gap: 8px;">
                <label style="white-space: nowrap;">
                  <input type="checkbox" name="behaviour[]" value="Lain-lain"> Lain-lain (Nyatakan):
                </label>
                <input type="text" name="behaviour_other" placeholder="Nyatakan" style="flex: 1;">
              </div>
            </div>
          </td>
        </tr>

        <tr>
          <td colspan="2">
            4. Kebolehan menguruskan diri:<br>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px 10px; padding-top: 5px;">
              <?php
                $self_care_options = ['Makan dan minum sendiri', ' Menggosok gigi', 'Pergi ke tandas', 'Mencuci tangan', 'Memakai baju'];
                foreach ($self_care_options as $option):
              ?>
              <label>
                <input type="checkbox" name="self_care[]" value="<?= $option ?>"> <?= $option ?>
              </label>
              <?php endforeach; ?>
            </div>
          </td>
        </tr>

        <tr>
          <td colspan="2">
            5. Kebolehan bergerak:<br>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px 10px; padding-top: 5px;">
              <?php
                $mobility_options = ['Berlari', ' Merangkak', 'Berjalan', 'Meniarap', 'Melompat', 'Baring'];
                foreach ($mobility_options as $option):
              ?>
              <label>
                <input type="checkbox" name="mobility[]" value="<?= $option ?>"> <?= $option ?>
              </label>
              <?php endforeach; ?>
            </div>
          </td>
        </tr>
      </table>

      <div class="form-button">
        <button type="button" class="btn-prev" onclick="showStep('stepB')">Sebelumnya</button>
        <button type="submit" class="btn-save">Simpan Borang</button>
      </div>

      </div>
    
    </form>
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

    <script>
    function showStep(stepId) {
      document.querySelectorAll('.step').forEach(div => div.classList.remove('active'));
      document.getElementById(stepId).classList.add('active');
    }

    function nextStep(stepId) {
      showStep(stepId);
      window.scrollTo(0, 0);
    }
  </script>

</body>
</html>