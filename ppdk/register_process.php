<?php
$con = mysqli_connect("localhost", "root", "", "ppdk_db") or die(mysqli_connect_errno($con));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = $_POST["name"];
    $email    = $_POST["email"];
    $phone    = $_POST["phone"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role     = $_POST["role"];
    $code     = $_POST["code"];

    // Kod khas sah mengikut role
    $validCodes = [
        'instructor' => 'INS123',
        'supervisor' => 'SV456',
        'chairman' => 'CH789'
    ];

    // Sahkan kata laluan sepadan
    if ($password !== $confirm_password) {
        echo "<script>alert('Kata laluan tidak sepadan!'); window.location.href = 'register.php';</script>";
        exit();
    }

    // Sahkan kod khas betul
    if (!isset($validCodes[$role]) || $code !== $validCodes[$role]) {
        echo "<script>alert('Kod khas tidak sah untuk peranan yang dipilih!'); window.location.href = 'register.php';</script>";
        exit();
    }

    // Semak sama ada username dah wujud dalam table user
    $checkQuery = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($con, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('ID Pengguna telah wujud. Sila pilih ID lain.'); window.location.href = 'register.php';</script>";
    } else {
        // Masukkan ke dalam table user
        $insert_query = "INSERT INTO users (name, email, phone, username, password, role)
                         VALUES ('$name', '$email', '$phone', '$username', '$password', '$role')";

        if (mysqli_query($con, $insert_query)) {
            // Paparan alert dalam Bahasa Melayu
            $roleDisplay = [
                'instructor' => 'Petugas',
                'supervisor' => 'Penyelia',
                'chairman' => 'Pengerusi'
            ];
            $role_malay = $roleDisplay[$role];

            echo "<script>alert('Pendaftaran berjaya sebagai $role_malay!'); window.location.href = 'login.php';</script>";
        } else {
            echo "<script>alert('Ralat semasa pendaftaran: " . mysqli_error($con) . "');</script>";
        }
    }
}
?>
