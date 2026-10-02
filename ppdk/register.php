<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pendaftaran</title>
  <link rel="stylesheet" href="css/register.css">
  <link rel="stylesheet" href="css/scroll.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
  <!-- Header -->
  <header>
    <img src="img/logo.jpg" alt="PDK INSANI Logo">
    <h1>SISTEM PENGURUSAN PPDK INSANI</h1>
    <nav>
        <a href="login.php"><i class="fas fa-right-to-bracket"></i> <span>LOG MASUK</span></a>
    </nav>
  </header>

  <!-- Registration form -->
  <main>
    <section class="register-form" id="register-form">
    <h3>Pendaftaran Pengguna</h3>
      <form name="register" method="post" action="register_process.php">
        <label for="name">Nama Penuh: <span class="required">*</span></label>
        <input type="text" id="name" name="name" required>

        <label for="email">Alamat E-mail: <span class="required">*</span></label>
        <input type="email" id="email" name="email" required>

        <label for="phone">No. Telefon Bimbit:</label>
        <input type="phone" id="phone" name="phone">

        <label for="username">ID Pengguna: <span class="required">*</span></label>
        <input type="username" id="username" name="username" required>

        <label for="password">Kata Laluan: <span class="required">*</span></label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Sahkan Kata Laluan: <span class="required">*</span></label>
        <input type="password" name="confirm_password" required><br>

        <label for="role">Pilih Peranan: <span class="required">*</span></label>
          <select id="role" name="role" required>
            <option value="" disabled selected>Sila pilih</option>
            <option value="instructor">Petugas</option>
            <option value="supervisor">Penyelia</option>
            <option value="chairman">Pengerusi</option>
          </select>

        <label for="code">Kod Khas (Peranan): <span class="required">*</span></label>
        <input type="text" id="code" name="code" required>

        <button type="submit">Daftar</button>
    </form>
    </section>
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
