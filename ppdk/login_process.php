<?php 
session_start();

$con = mysqli_connect("localhost", "root", "", "ppdk_db") or die(mysqli_connect_errno($con));

if (isset($_POST["username"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $role     = $_POST["role"];

    // Paparan dalam BM
    $role_malay = [
        'instructor' => 'Petugas',
        'supervisor' => 'Penyelia',
        'chairman'   => 'Pengerusi'
    ];

    // Tentukan redirect page ikut role
    $redirect_pages = [
        'instructor' => 'index.php',
        'supervisor' => 'sv_index.php',
        'chairman'   => 'ch_index.php'
    ];

    // Semak role sah
    if (!array_key_exists($role, $redirect_pages)) {
        echo "<script>alert('Peranan tidak sah!'); window.location.href = 'login.php';</script>";
        exit;
    }

    // Cari user dalam jadual user
    $query = "SELECT * FROM users WHERE username = '$username' AND role = '$role'";
    $result = mysqli_query($con, $query) or die(mysqli_error($con));

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        // Semak kata laluan (plain text – sila tukar ke password_hash() untuk production)
        if ($row['password'] === $password) {
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $role;

            // ✅ Update waktu login terakhir
            $update_query = "UPDATE users SET last_login = NOW() WHERE username = '$username'";
            mysqli_query($con, $update_query);

            echo "<script>window.location.href = '{$redirect_pages[$role]}';</script>";
            exit;
        } else {
            echo "<script>alert('Kata laluan salah.'); window.location.href = 'login.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Pengguna tidak ditemui atau peranan salah.'); window.location.href = 'login.php';</script>";
        exit;
    }
}
?>
