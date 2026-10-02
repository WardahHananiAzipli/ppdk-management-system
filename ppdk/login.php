<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log Masuk</title>
  <link rel="stylesheet" href="css/login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<!-- Login form -->
<body>
  <div class="overlay" id="popupOverlay">
    <div class="popup">
      <img src="img/logo.jpg" alt="Logo PPDK INSANI">
      <br>     
      <h2>Log Masuk</h2>
      <p class="subheading">Sistem Pengurusan PPDK INSANI</p>

      <form name="login" method="post" action="login_process.php">
        <hr style="margin: 20px 0; border: 0; border-top: 2px solid #ccc;">
        <div class="role-radio">
          <label><input type="radio" name="role" value="instructor" required> Petugas</label>
          <label><input type="radio" name="role" value="supervisor"> Penyelia</label>
          <label><input type="radio" name="role" value="chairman"> Pengerusi</label>
        </div>
    
        <label for="username">ID Pengguna:</label>
        <input type="text" id="username" name="username" placeholder="ID Pengguna" required
        style="width: 100%; padding: 10px; box-sizing: border-box;" />

        <br>

        <label for="password">Kata Laluan:</label>
          <div style="position: relative;">
            <input type="password" id="password" name="password" placeholder="Kata Laluan" required
                  style="width: 100%; padding: 10px; padding-right: 40px; box-sizing: border-box;" />
            
            <i class="fa-solid fa-eye-slash" id="togglePassword"
              onclick="togglePassword()"
              style="position: absolute; right: 12px; top: 35%; transform: translateY(-50%); cursor: pointer; color: #888;">
            </i>
          </div>

        <br>

        <button type="submit">Log Masuk</button>

        <div class="links">
          <a href="register.php">Belum mempunyai akaun? Klik di sini untuk daftar</a>
        </div>
      </form>

    </div>
  </div>

  <!-- Password hide -->
  <script src="js/password.js"></script>

</body>
</html>