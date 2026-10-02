<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ppdk_db');

// Dapatkan data pengguna
$username = $_SESSION['username'];
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$user_id = $data['user_id'];
$profilePic = !empty($data['profile_pic']) ? 'uploads/' . $data['profile_pic'] : 'img/profile-placeholder.jpg';

function getPerformance($conn, $trainee_id) {
    $stmt = $conn->prepare("
        SELECT week, roll_left_right, reach_forward, sit, crawl, creep, kneel, stand, climb_stairs, descend_stairs,
        swallow_safely, wash_hands_before_after, bring_food_to_mouth, eat_with_hand, eat_with_spoon, drink_with_cup, clean_utensils, arrange_utensils,
        identify_front_back, remove_clothing, put_on_clothes, put_on_socks_shoes, button_up, comb_hair, apply_powder,
        know_when_toilet, tell_need_toilet, go_to_toilet, understand_toilet_use, clean_after_toilet, manage_menstruation,
        go_to_bathroom, pour_water_on_body, soap_and_clean_body, dry_body_with_towel, brush_teeth, put_toothpaste_on_brush,
        fold_clothes, wash_own_dishes, prepare_drink, wash_own_clothes, take_care_siblings, do_simple_chores,
        identify_basic_shapes, understand_basic_colors, understand_number_concept, write_some_numbers, write_some_letters, write_some_words, 
        write_own_name, know_own_name, differentiate_big_small, know_left_right, know_top_bottom, express_hungry_full, 
        understand_right_wrong, name_body_parts, tell_gender_when_shown, understand_family_relation, name_family_members, respond_to_greeting,
        play_independently, play_with_friends, vocalize_during_play, imitate_during_play, constructive_block_play, share_toys_with_friends, cooperate_in_group_play, take_turns_in_group_activity, tidy_up_toys,
        say_own_name, respond_to_name_call, look_at_speaker, understand_yes_no, answer_what_is_this, say_simple_things, tell_story_from_picture, answer_phone_call,
        wander_without_purpose, restless_or_cannot_stay_put, run_without_reason, spit_pinch_kick, passive_no_reaction, use_vulgar_language, use_incomprehensible_words,
        break_things, undress_inappropriately, deliberately_disobey, sleep_during_day, age_adjustment_difficulty, extreme_behaviour, understand_rules_discipline,
        show_affection_to_peers, argue_when_needs_unmet, resolve_conflict_peacefully, worry_when_friends_in_trouble, express_anger, use_rude_language, use_unintelligible_language, attempt_help_parents_teachers,
        pick_up_objects_efficiently, use_both_hands_well, stand_jump_on_one_leg, unscrew_container_lid, differentiate_by_size, differentiate_texture, cut_along_straight_line, understand_money_up_to_100, travel_independently_public_transport, have_savings_account_concept
        FROM trainee_performance 
        WHERE trainee_id = ?
        ORDER BY week ASC
    ");
    $stmt->bind_param("i", $trainee_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Untuk checkbox multiple
function is_checked($value, $data) {
    $items = json_decode($data, true);
    if (!is_array($items)) {
        $items = explode(",", $data);
    }
    return in_array(trim($value), array_map('trim', $items)) ? "checked" : "";
}

// Ambil semua pelatih
$trainee_result = $conn->query("SELECT * FROM trainee ORDER BY full_name ASC");
?>

<?php
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
    <title>Borang Kemajuan Pelatih</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/scroll.css">
    <link rel="stylesheet" href="css/performance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
        function fetchTrainee() {
            const id = document.getElementById("trainee_id").value;
            if (!id) return;

            fetch("trainee_performance_process.php?action=fetch&id=" + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("ic").value = data.ic || "";
                    document.getElementById("dob").value = data.dob || "";
                    document.getElementById("gender_male").checked = (data.gender === "Lelaki");
                    document.getElementById("gender_female").checked = (data.gender === "Perempuan");
                });
        }
    </script>

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

<!-- Main -->
<div class="main-content">
<main class="printable-form">
<form action="trainee_performance_process.php" method="POST">
<?php while ($trainee = $trainee_result->fetch_assoc()): ?>

<!-- SECTION A -->
<div id="stepA" class="step active">
    <div style="margin-top:20px; text-align: center; font-weight: bold;">LAMPIRAN D<br>BORANG KEMAJUAN PELATIH</div>
    
    <div class="section" style="margin-left: 55px;">
        <b>LAPORAN PENILAIAN KEMAJUAN PELATIH OKU DI PDK</b><br>
        1. Penilaian seterusnya dibuat tiap (3) bulan sekali.<br>
        2. Borang Penilaian ini digunakan di PDK dan Lawatan Pemulihan di rumah.<br>
        3. Borang ini mesti disimpan di dalam fail peribadi pelatih OKU.<br><br>
    </div>

    <div class="section">
        <b style="margin-left: 55px;">A. BUTIRAN DIRI PELATIH</b><br><br>
        
        <table>
            <tr>
                <td>1. Nama Penuh</td>
                <td>: <?= show_field($trainee['full_name']) ?></td>
            </tr>
            <tr>
                <td>2. No KP/ Sijil Beranak</td>
                <td>: <?= show_field($trainee['ic']) ?></td>
            </tr>
            <tr>
                <td>3. Tarikh Lahir</td>
                <td>: <?= show_field($trainee['dob']) ?></td>
            </tr>
            <tr>
                <td>4. Jantina</td>
                <td>: <?= show_field($trainee['gender']) ?></td>
            </tr>

            <tr>
                <td>5. Kategori OKU</td>
                <td class="checkbox-group">
                    <?php
                    $categories = ['Pertuturan', 'Masalah Pembelajaran', 'Lumpuh', 'Pendengaran', 'Penglihatan', 'Fizikal', 'Mental', 'Pelbagai'];
                   
                    foreach ($categories as $cat): ?>
                        <label><input type="checkbox" <?= is_checked($cat, $trainee['oku_categories']) ?> disabled> <?= $cat ?></label><br>
                    <?php endforeach; ?>
                </td>
            </tr>

            <tr>
                <td>6. Jika menggunakan alat bantuan, jenis alat bantuan</td>
                <td class="checkbox-group">
                    <?php
                    $assistive_tool = ['Kerusi Roda', 'Tongkat', 'Walking Frame', 'Kasut Khas', 'Kaliber', 'Anggota Palsu', 'Alat Pendengaran', 'Cermin Mata Khas', 'Lain-lain'];

                    foreach ($assistive_tool as $item): ?>
                        <label><input type="checkbox" <?= is_checked($item, $trainee['assistive_tools_used']) ?> disabled> <?= $item ?></label><br>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <td>7. Jika belum, alat bantuan yang diperlukan ialah</td>
                <td>: <?= show_field($trainee['assistive_tools_needed'] ?? '') ?></td>
            </tr>
        </table>

        <div class="form-button">
            <button type="button" class="btn-next-1" onclick="nextStep('stepB')">Seterusnya</button>
        </div>
    </div>
</div>

<!-- SECTION B -->
<div class="page-break"></div> 
<div id="stepB" class="step" style="margin-top: 30px;">
    <h3>B. KEMAHIRAN MOTOR KASAR</h3>
    <p>Sila tandakan <strong>Y</strong> (Boleh melakukan sendiri), <strong>B</strong> (Boleh, tetapi dengan bantuan), <strong>T</strong> (Tidak boleh melakukan langsung)</p>

    <?php
        $motor_skills = [
            "roll_left_right" => "Bergolek ke kiri dan kanan",
            "reach_forward" => "Menjulur ke hadapan",
            "sit" => "Duduk",
            "crawl" => "Mengesot",
            "creep" => "Merangkak",
            "kneel" => "Melutut",
            "stand" => "Berdiri",
            "climb_stairs" => "Menaiki tangga",
            "descend_stairs" => "Turun tangga"
        ];
    ?>

    <?php
        $week_data = [];
        while ($row = $performance_result->fetch_assoc()) {
            $week_data[$row['week']] = $row;
        }
    ?>

    <table>
        <thead>
        <tr>
            <th rowspan="2">Bil</th>
            <th rowspan="2">Kebolehan</th>
            <th colspan="12">Minggu</th>
        </tr>
        <tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <th colspan="3"><?= $w ?></th>
            <?php endfor; ?>
        </tr>
        <tr>
            <td colspan="2"></td>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <td>Y</td><td>B</td><td>T</td>
            <?php endfor; ?>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($motor_skills as $key => $label): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $label ?></td>
                <?php for ($w = 1; $w <= 4; $w++): ?>
                    <?php
                    $value = $week_data[$w][$key] ?? '';
                    ?>
                    <?php foreach (['Y', 'B', 'T'] as $opt): ?>
                        <td>
                            <input type="radio" <?= $value == $opt ? 'checked' : '' ?> disabled>
                        </td>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <br><br>

    <!-- SECTION C -->
    <div class="page-break"></div> 
    <h3>C. MAKAN MINUM</h3>
    <p>Sila tandakan <strong>Y</strong> (Boleh melakukan sendiri), <strong>B</strong> (Boleh, tetapi dengan bantuan), <strong>T</strong> (Tidak boleh melakukan langsung)</p>
    <?php
        $motor_skills = [
            "swallow_safely" => "Menelan dengan selamat",
            "wash_hands_before_after" => "Membasuh tangan sebelum/selepas makan",
            "bring_food_to_mouth" => "Mengambil makanan dan meletakkan ke dalam mulut",
            "eat_with_hand" => "Makan sendiri dengan tangan",
            "eat_with_spoon" => "Makan sendiri dengan sudu",
            "drink_with_cup" => "Minum sendiri gunakan cawan",
            "clean_utensils" => "Membersihkan perkakas",
            "arrange_utensils" => "Menyusun perkakas di tempat yang sesuai"
        ];
    ?>

    <table>
        <thead>
        <tr>
            <th rowspan="2">Bil</th>
            <th rowspan="2">Kebolehan</th>
            <th colspan="12">Minggu</th>
        </tr>
        <tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <th colspan="3"><?= $w ?></th>
            <?php endfor; ?>
        </tr>
        <tr>
            <td colspan="2"></td>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <td>Y</td><td>B</td><td>T</td>
            <?php endfor; ?>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($motor_skills as $key => $label): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $label ?></td>
                <?php for ($w = 1; $w <= 4; $w++): ?>
                    <?php
                    $value = $week_data[$w][$key] ?? '';
                    ?>
                    <?php foreach (['Y', 'B', 'T'] as $opt): ?>
                        <td>
                            <input type="radio" <?= $value == $opt ? 'checked' : '' ?> disabled>
                        </td>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="form-button">
        <button type="button" class="btn-prev" onclick="showStep('stepA')">Sebelumnya</button>
        <button type="button" class="btn-next" onclick="nextStep('stepD')">Seterusnya</button>
    </div>
</div>

<!-- SECTION D -->
<div class="page-break"></div> 
<div id="stepD" class="step" style="margin-top: 30px;">
    <h3>D. PEMAKAIAN</h3>
    <p>Sila tandakan <strong>Y</strong> (Boleh melakukan sendiri), <strong>B</strong> (Boleh, tetapi dengan bantuan), <strong>T</strong> (Tidak boleh melakukan langsung)</p>

    <?php
    $motor_skills = [
        "identify_front_back"   => "Boleh membezakan bahagian hadapan/belakang",
        "remove_clothing"       => "Menanggalkan pakaian",
        "put_on_clothes"        => "Memakai seluar/baju",
        "put_on_socks_shoes"    => "Memakai sarung kaki/kasut",
        "button_up"             => "Memasang butang",
        "comb_hair"             => "Menyikat rambut",
        "apply_powder"          => "Memakai bedak di muka"
    ];
    ?>

    <table>
        <thead>
        <tr>
            <th rowspan="2">Bil</th>
            <th rowspan="2">Kebolehan</th>
            <th colspan="12">Minggu</th>
        </tr>
        <tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <th colspan="3"><?= $w ?></th>
            <?php endfor; ?>
        </tr>
        <tr>
            <td colspan="2"></td>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <td>Y</td><td>B</td><td>T</td>
            <?php endfor; ?>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($motor_skills as $key => $label): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $label ?></td>
                <?php for ($w = 1; $w <= 4; $w++): ?>
                    <?php
                    $value = $week_data[$w][$key] ?? '';
                    ?>
                    <?php foreach (['Y', 'B', 'T'] as $opt): ?>
                        <td>
                            <input type="radio" <?= $value == $opt ? 'checked' : '' ?> disabled>
                        </td>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <br><br>
    
    <!-- SECTION E -->
    <div class="page-break"></div> 
    <h3>E. PENGGUNAAN TANDAS</h3>
    <p>Sila tandakan <strong>Y</strong> (Boleh melakukan sendiri), <strong>B</strong> (Boleh, tetapi dengan bantuan), <strong>T</strong> (Tidak boleh melakukan langsung)</p>
    <?php
    $motor_skills = [
        "know_when_toilet"         => "Tahu bila hendak ke tandas untuk buang air kecil/besar",
        "tell_need_toilet"         => "Boleh memberitahu untuk ke tandas",
        "go_to_toilet"             => "Pergi ke tandas untuk buang air kecil/besar",
        "understand_toilet_use"    => "Memahami kegunaan tandas",
        "clean_after_toilet"       => "Membersihkan diri selepas menggunakan tandas",
        "manage_menstruation"      => "Menguruskan diri sendiri semasa kedatangan haid (bagi perempuan)"
    ];
    ?>

    <table>
        <thead>
        <tr>
            <th rowspan="2">Bil</th>
            <th rowspan="2">Kebolehan</th>
            <th colspan="12">Minggu</th>
        </tr>
        <tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <th colspan="3"><?= $w ?></th>
            <?php endfor; ?>
        </tr>
        <tr>
            <td colspan="2"></td>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <td>Y</td><td>B</td><td>T</td>
            <?php endfor; ?>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($motor_skills as $key => $label): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $label ?></td>
                <?php for ($w = 1; $w <= 4; $w++): ?>
                    <?php
                    $value = $week_data[$w][$key] ?? '';
                    ?>
                    <?php foreach (['Y', 'B', 'T'] as $opt): ?>
                        <td>
                            <input type="radio" <?= $value == $opt ? 'checked' : '' ?> disabled>
                        </td>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="form-button">
        <button type="button" class="btn-prev" onclick="showStep('stepB')">Sebelumnya</button>
        <button type="button" class="btn-next" onclick="nextStep('stepF')">Seterusnya</button>
    </div>
</div>

<!-- SECTION F -->
<div class="page-break"></div> 
<div id="stepF" class="step" style="margin-top: 30px;">
    <h3>F. MANDI</h3>
    <p>Sila tandakan <strong>Y</strong> (Boleh melakukan sendiri), <strong>B</strong> (Boleh, tetapi dengan bantuan), <strong>T</strong> (Tidak boleh melakukan langsung)</p>

    <?php
    $motor_skills = [
        "go_to_bathroom"          => "Pergi ke bilik mandi sendiri",
        "pour_water_on_body"      => "Menyiram air ke tubuh badan",
        "soap_and_clean_body"     => "Menyabun/membersih tubuh",
        "dry_body_with_towel"     => "Mengelap tubuh badan dengan tuala",
        "brush_teeth"             => "Memberus gigi",
        "put_toothpaste_on_brush" => "Letakkan ubat gigi di atas berus gigi"
    ];
    ?>

    <table>
        <thead>
        <tr>
            <th rowspan="2">Bil</th>
            <th rowspan="2">Kebolehan</th>
            <th colspan="12">Minggu</th>
        </tr>
        <tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <th colspan="3"><?= $w ?></th>
            <?php endfor; ?>
        </tr>
        <tr>
            <td colspan="2"></td>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <td>Y</td><td>B</td><td>T</td>
            <?php endfor; ?>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($motor_skills as $key => $label): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $label ?></td>
                <?php for ($w = 1; $w <= 4; $w++): ?>
                    <?php
                    $value = $week_data[$w][$key] ?? '';
                    ?>
                    <?php foreach (['Y', 'B', 'T'] as $opt): ?>
                        <td>
                            <input type="radio" <?= $value == $opt ? 'checked' : '' ?> disabled>
                        </td>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <br><br>
    
    <!-- SECTION G -->
    <div class="page-break"></div> 
    <h3>G. MEMBUAT KERJA RUMAH</h3>
    <p>Sila tandakan <strong>Y</strong> (Boleh melakukan sendiri), <strong>B</strong> (Boleh, tetapi dengan bantuan), <strong>T</strong> (Tidak boleh melakukan langsung)</p>
    <?php
    $motor_skills = [
        "fold_clothes"        => "Melipat kain",
        "wash_own_dishes"     => "Membasuh pinggan/cawan sendiri",
        "prepare_drink"       => "Menyediakan minum",
        "wash_own_clothes"    => "Membasuh baju sendiri",
        "take_care_siblings"  => "Menjaga adik beradik",
        "do_simple_chores"    => "Membuat kerja rumah yang senang (menyapu dll)"
    ];
    ?>

    <table>
        <thead>
        <tr>
            <th rowspan="2">Bil</th>
            <th rowspan="2">Kebolehan</th>
            <th colspan="12">Minggu</th>
        </tr>
        <tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <th colspan="3"><?= $w ?></th>
            <?php endfor; ?>
        </tr>
        <tr>
            <td colspan="2"></td>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <td>Y</td><td>B</td><td>T</td>
            <?php endfor; ?>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($motor_skills as $key => $label): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $label ?></td>
                <?php for ($w = 1; $w <= 4; $w++): ?>
                    <?php
                    $value = $week_data[$w][$key] ?? '';
                    ?>
                    <?php foreach (['Y', 'B', 'T'] as $opt): ?>
                        <td>
                            <input type="radio" <?= $value == $opt ? 'checked' : '' ?> disabled>
                        </td>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="form-button">
        <button type="button" class="btn-prev" onclick="showStep('stepD')">Sebelumnya</button>
        <button type="button" class="btn-next" onclick="nextStep('stepH')">Seterusnya</button>
    </div>
</div>

<!-- SECTION H -->
<div class="page-break"></div> 
<div id="stepH" class="step" style="margin-top: 30px;">
    <h3>H. KECERDASAN</h3>
    <p>Sila tandakan <strong>Y</strong> (Boleh melakukan sendiri), <strong>B</strong> (Boleh, tetapi dengan bantuan), <strong>T</strong> (Tidak boleh melakukan langsung)</p>

    <?php
    $motor_skills = [
        "identify_basic_shapes"       => "Membezakan bentuk asas seperti: empat segi, tiga segi, bulat",
        "understand_basic_colors"     => "Memahami warna asas",
        "understand_number_concept"   => "Mengerti konsep nombor",
        "write_some_numbers"          => "Menulis beberapa nombor",
        "write_some_letters"          => "Menulis beberapa abjad",
        "write_some_words"            => "Menulis beberapa perkataan",
        "write_own_name"              => "Menulis nama sendiri",
        "know_own_name"               => "Mengetahui nama sendiri",
        "differentiate_big_small"     => "Boleh bezakan besar /kecil",
        "know_left_right"             => "Tahu kiri/kanan",
        "know_top_bottom"             => "Tahu atas/bawah",
        "express_hungry_full"         => "Memberitahu apabila ia sudah lapar/kenyang",
        "understand_right_wrong"      => "Memahami konsep betul/salah",
        "name_body_parts"             => "Menamakan bahagian badan",
        "tell_gender_when_shown"      => "Memberitahu jantina apabila ditunjukkan",
        "understand_family_relation"  => "Memahami mengenai perhubungan ahli keluarga",
        "name_family_members"         => "Menamakan ahli keluarga",
        "respond_to_greeting"         => "Bertindakbalas kepada ucapan salam dan berjabat tangan"
    ];
    ?>

    <table>
        <thead>
        <tr>
            <th rowspan="2">Bil</th>
            <th rowspan="2">Kebolehan</th>
            <th colspan="12">Minggu</th>
        </tr>
        <tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <th colspan="3"><?= $w ?></th>
            <?php endfor; ?>
        </tr>
        <tr>
            <td colspan="2"></td>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <td>Y</td><td>B</td><td>T</td>
            <?php endfor; ?>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($motor_skills as $key => $label): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $label ?></td>
                <?php for ($w = 1; $w <= 4; $w++): ?>
                    <?php
                    $value = $week_data[$w][$key] ?? '';
                    ?>
                    <?php foreach (['Y', 'B', 'T'] as $opt): ?>
                        <td>
                            <input type="radio" <?= $value == $opt ? 'checked' : '' ?> disabled>
                        </td>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <br><br>

    <!-- SECTION I -->
    <div class="page-break"></div> 
    <h3>I. KEBOLEHAN BERMAIN</h3>
    <p>Sila tandakan <strong>Y</strong> (Boleh melakukan sendiri), <strong>B</strong> (Boleh, tetapi dengan bantuan), <strong>T</strong> (Tidak boleh melakukan langsung)</p>
    <?php
    $motor_skills = [
        "play_independently"            => "Bermain sendiri",
        "play_with_friends"             => "Bermain dengan rakan-rakan",
        "vocalize_during_play"          => "Mengeluarkan suara semasa bermain",
        "imitate_during_play"           => "Meniru perbuatan semasa bermain",
        "constructive_block_play"       => "Bermain dengan blok-blok secara konstruktif",
        "share_toys_with_friends"       => "Berkongsi alat permainan dengan rakan lain",
        "cooperate_in_group_play"       => "Bekerjasama semasa bermain secara kumpulan",
        "take_turns_in_group_activity"  => "Menunggu giliran semasa kegiatan kumpulan",
        "tidy_up_toys"                  => "Mengemaskan alat -alat permainan"
    ];
    ?>

    <table>
        <thead>
        <tr>
            <th rowspan="2">Bil</th>
            <th rowspan="2">Kebolehan</th>
            <th colspan="12">Minggu</th>
        </tr>
        <tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <th colspan="3"><?= $w ?></th>
            <?php endfor; ?>
        </tr>
        <tr>
            <td colspan="2"></td>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <td>Y</td><td>B</td><td>T</td>
            <?php endfor; ?>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($motor_skills as $key => $label): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $label ?></td>
                <?php for ($w = 1; $w <= 4; $w++): ?>
                    <?php
                    $value = $week_data[$w][$key] ?? '';
                    ?>
                    <?php foreach (['Y', 'B', 'T'] as $opt): ?>
                        <td>
                            <input type="radio" <?= $value == $opt ? 'checked' : '' ?> disabled>
                        </td>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="form-button">
        <button type="button" class="btn-prev" onclick="showStep('stepF')">Sebelumnya</button>
        <button type="button" class="btn-next" onclick="nextStep('stepJ')">Seterusnya</button>
    </div>
</div>

<!-- SECTION J -->
<div class="page-break"></div> 
<div id="stepJ" class="step" style="margin-top: 30px;">
    <h3>J. PERTUTURAN</h3>
    <p>Sila tandakan <strong>Y</strong> (Boleh melakukan sendiri), <strong>B</strong> (Boleh, tetapi dengan bantuan), <strong>T</strong> (Tidak boleh melakukan langsung)</p>

    <?php
    $motor_skills = [
        "say_own_name"           => "Menyebut nama sendiri",
        "respond_to_name_call"   => "Bertindak balas apabila nama disebut",
        "look_at_speaker"        => "Memandang muka orang yang bercakap dengannya",
        "understand_yes_no"      => "Fahami kenyataan YA atau TIDAK",
        "answer_what_is_this"    => "Menjawab soalan 'apa ini?' kepada benda yang biasa",
        "say_simple_things"      => "Bercakap perkara yang mudah",
        "tell_story_from_picture"=> "Bercerita mengenai gambar",
        "answer_phone_call"      => "Menjawab panggilan telefon"
    ];
    ?>

    <table>
        <thead>
        <tr>
            <th rowspan="2">Bil</th>
            <th rowspan="2">Kebolehan</th>
            <th colspan="12">Minggu</th>
        </tr>
        <tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <th colspan="3"><?= $w ?></th>
            <?php endfor; ?>
        </tr>
        <tr>
            <td colspan="2"></td>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <td>Y</td><td>B</td><td>T</td>
            <?php endfor; ?>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($motor_skills as $key => $label): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $label ?></td>
                <?php for ($w = 1; $w <= 4; $w++): ?>
                    <?php
                    $value = $week_data[$w][$key] ?? '';
                    ?>
                    <?php foreach (['Y', 'B', 'T'] as $opt): ?>
                        <td>
                            <input type="radio" <?= $value == $opt ? 'checked' : '' ?> disabled>
                        </td>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <br><br>

    <!-- SECTION K -->
    <div class="page-break"></div> 
    <h3>K. KELAKUAN</h3>
    <p>Sila tandakan <strong>Y</strong> (Boleh melakukan sendiri), <strong>B</strong> (Boleh, tetapi dengan bantuan), <strong>T</strong> (Tidak boleh melakukan langsung)</p>
    <?php
    $motor_skills = [
        "wander_without_purpose"       => "Merayau-rayau daripada rumah tanpa tujuan",
        "restless_or_cannot_stay_put"  => "Suka merayau/tidak duduk setempat",
        "run_without_reason"           => "Berlari tanpa sebab/bertukar kepada kegiatan yang lain tanpa menjalankan aktiviti yang diarahkan",
        "spit_pinch_kick"              => "Meludah/mencubit/menendang tanpa sebab",
        "passive_no_reaction"          => "Pasif dan tidak bertindak",
        "use_vulgar_language"          => "Menggunakan bahasa yang kesat dan lucah",
        "use_incomprehensible_words"   => "Menggunakan bahasa yang tidak difahami",
        "break_things"                 => "Suka memecahkan barang",
        "undress_inappropriately"      => "Suka membuka pakaian pada masa yang tidak tentu",
        "deliberately_disobey"         => "Mengingkari arahan dengan sengaja",
        "sleep_during_day"             => "Tidur waktu siang",
        "age_adjustment_difficulty"    => "Mengalami masalah menyesuaikan diri dengan perubahan umur",
        "extreme_behaviour"            => "Kelakuan yang luar biasa (berbogel, meraba, dll)",
        "understand_rules_discipline"  => "Memahami disiplin/peraturan"
    ];
    ?>

    <table>
        <thead>
        <tr>
            <th rowspan="2">Bil</th>
            <th rowspan="2">Kebolehan</th>
            <th colspan="12">Minggu</th>
        </tr>
        <tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <th colspan="3"><?= $w ?></th>
            <?php endfor; ?>
        </tr>
        <tr>
            <td colspan="2"></td>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <td>Y</td><td>B</td><td>T</td>
            <?php endfor; ?>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($motor_skills as $key => $label): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $label ?></td>
                <?php for ($w = 1; $w <= 4; $w++): ?>
                    <?php
                    $value = $week_data[$w][$key] ?? '';
                    ?>
                    <?php foreach (['Y', 'B', 'T'] as $opt): ?>
                        <td>
                            <input type="radio" <?= $value == $opt ? 'checked' : '' ?> disabled>
                        </td>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="form-button">
        <button type="button" class="btn-prev" onclick="showStep('stepH')">Sebelumnya</button>
        <button type="button" class="btn-next" onclick="nextStep('stepL')">Seterusnya</button>
    </div>
</div>

<!-- SECTION L -->
<div class="page-break"></div> 
<div id="stepL" class="step" style="margin-top: 30px;">
    <h3>L. PERASAAN/EMOSI</h3>
    <p>Sila tandakan <strong>Y</strong> (Boleh melakukan sendiri), <strong>B</strong> (Boleh, tetapi dengan bantuan), <strong>T</strong> (Tidak boleh melakukan langsung)</p>

    <?php
    $motor_skills = [
        "show_affection_to_peers"        => "Menunjukkan kasih sayang kepada adik beradik/rakan sebaya",
        "argue_when_needs_unmet"         => "Bertengkar dengan rakan-rakan apabila kehendak tidak dituruti",
        "resolve_conflict_peacefully"    => "Memahami cara untuk mengatasi, perselisahan faham dengan memberi alasan lebih baik dari memukul",
        "worry_when_friends_in_trouble"  => "Menunjukkan perasaan khuatir apabila rakan di dalam kesusahan",
        "express_anger"                  => "Menunjukkan perasaan marahnya",
        "use_rude_language"              => "Menggunakan bahasa yang kesal/lucah",
        "use_unintelligible_language"    => "Menggunakan bahasa yang tidak difahami",
        "attempt_help_parents_teachers"  => "Cuba menolong guru/ibubapa"
    ];
    ?>

    <table>
        <thead>
        <tr>
            <th rowspan="2">Bil</th>
            <th rowspan="2">Kebolehan</th>
            <th colspan="12">Minggu</th>
        </tr>
        <tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <th colspan="3"><?= $w ?></th>
            <?php endfor; ?>
        </tr>
        <tr>
            <td colspan="2"></td>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <td>Y</td><td>B</td><td>T</td>
            <?php endfor; ?>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($motor_skills as $key => $label): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $label ?></td>
                <?php for ($w = 1; $w <= 4; $w++): ?>
                    <?php
                    $value = $week_data[$w][$key] ?? '';
                    ?>
                    <?php foreach (['Y', 'B', 'T'] as $opt): ?>
                        <td>
                            <input type="radio" <?= $value == $opt ? 'checked' : '' ?> disabled>
                        </td>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <br><br>

    <!-- SECTION M -->
    <div class="page-break"></div> 
    <h3>M. KEMAHIRAN PRA VOKASIONAL</h3>
    <p>Sila tandakan <strong>Y</strong> (Boleh melakukan sendiri), <strong>B</strong> (Boleh, tetapi dengan bantuan), <strong>T</strong> (Tidak boleh melakukan langsung)</p>
    <?php
    $motor_skills = [
        "pick_up_objects_efficiently"         => "Mengutip benda-benda kecil/besar dengan cekap",
        "use_both_hands_well"                 => "Menggunakan kedua-dua tangan cekap",
        "stand_jump_on_one_leg"              => "Berdiri/Melompat dengan sebelah kaki",
        "unscrew_container_lid"              => "Membuka/Mengutip penutup bekas (unscrewing)",
        "differentiate_by_size"              => "Membezakan benda mengikut saiz",
        "differentiate_texture"              => "Membezakan kelembutan/kekerasan sesuatu benda",
        "cut_along_straight_line"            => "Menggunting mengikut garisan yang lurus",
        "understand_money_up_to_100"         => "Memahami penggunaan wang bernilai hingga RM100",
        "travel_independently_public_transport" => "Membuat perjalanan sendiri (menggunakan bas awam/teksi)",
        "have_savings_account_concept"       => "Mempunyai konsep simpan wang (Mempunyai akaun bank)"
    ];
    ?>

    <table>
        <thead>
        <tr>
            <th rowspan="2">Bil</th>
            <th rowspan="2">Kebolehan</th>
            <th colspan="12">Minggu</th>
        </tr>
        <tr>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <th colspan="3"><?= $w ?></th>
            <?php endfor; ?>
        </tr>
        <tr>
            <td colspan="2"></td>
            <?php for ($w = 1; $w <= 4; $w++): ?>
                <td>Y</td><td>B</td><td>T</td>
            <?php endfor; ?>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($motor_skills as $key => $label): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $label ?></td>
                <?php for ($w = 1; $w <= 4; $w++): ?>
                    <?php
                    $value = $week_data[$w][$key] ?? '';
                    ?>
                    <?php foreach (['Y', 'B', 'T'] as $opt): ?>
                        <td>
                            <input type="radio" <?= $value == $opt ? 'checked' : '' ?> disabled>
                        </td>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="form-button">
        <a href="trainee_performance.php" class="btn-prev">Kembali</a>
        <button type="button" class="btn-print" onclick="printAllSections()">Cetak Laporan Prestasi Pelatih</button>
    </div>
</div>
    <hr>
<?php endwhile; ?>
</form>
</div>
</div>

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

    <script>
    function printAllSections() {
        // Tambah class 'active' pada semua .step supaya semua section muncul untuk print
        const steps = document.querySelectorAll('.step');
        steps.forEach(step => {
            step.classList.add('active');
        });

        // Cetak
        window.print();

        // Selepas print, aktifkan semula hanya section pertama
        steps.forEach(step => {
            step.classList.remove('active');
        });
        document.getElementById('stepA').classList.add('active');
    }
    </script>

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
