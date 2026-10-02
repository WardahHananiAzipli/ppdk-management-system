<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ppdk_db');
if ($conn->connect_error) {
    die("Ralat DB: " . $conn->connect_error);
}
$username = $_SESSION['username'];
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$profilePic = !empty($data['profile_pic']) ? 'uploads/' . $data['profile_pic'] : 'img/profile-placeholder.jpg';
$updateSuccess = isset($_GET['update']) && $_GET['update'] === 'success';
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Prestasi Pelatih</title>

    <!-- CSS Asal -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/scroll.css">
    <link rel="stylesheet" href="css/report.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <!-- Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

    <style>
      /* Kekalkan gaya anda, tambah kecil jika perlu */
      .filter-container {
        margin: 0 auto 20px auto;
        background: #fff;
        padding: 16px 20px;
        border-radius: 10px;
        box-shadow: 0 0 5px #ccc;
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
      }
      .filter-container label, .filter-container select {
        font-size: 14px;
      }
      .table-container {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 5px #ccc;
      }
      .main-container {
        margin-left: 270px;
        max-width: 100%;
        padding: 20px;
        background: white;
        color: black;
        box-sizing: border-box;
      }
      h2 {
        color: #333;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1rem;
      }
      /* Lebar table penuh + scroll mendatar */
      div.dataTables_wrapper {
        width: 100%;
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

  <h1><?= htmlspecialchars($data['username'] ?? '') ?></h1>
  <a href="ch_index.php">Profil</a>

  <div class="dropdown">
    <button class="dropdown-btn">Laporan Pengurusan</button>
    <div class="dropdown-content">

      <!-- Submenu: Pendaftaran -->
      <div class="sub-dropdown">
        <button class="sub-dropdown-btn">Pendaftaran</button>
        <div class="sub-dropdown-content">
          <a href="report_staff_registration.php">Kakitangan</a>
          <a href="report_trainee_registration.php">Pelatih</a>
        </div>
      </div>

      <!-- Submenu: Kehadiran -->
      <div class="sub-dropdown">
        <button class="sub-dropdown-btn">Kehadiran</button>
        <div class="sub-dropdown-content">
          <a href="report_staff_attendance.php">Kakitangan</a>
          <a href="report_trainee_attendance.php">Pelatih</a>
        </div>
      </div>

      <!-- Pautan terus -->
      <a href="report_trainee_allowance.php">Elaun Pelatih</a>
      <a href="report_trainee_performance.php">Laporan Kemajuan Pelatih</a>

    </div>
  </div>
</div>

<!-- Main Body -->
<div class="main-container">
  <h2>Laporan Prestasi Pelatih</h2>

  <!-- Filter: Nama Pelatih & Minggu -->
  <div class="filter-container">
    <label for="trainee_perf_id">Nama Pelatih:</label>
    <select id="trainee_perf_id">
      <option value="">Semua</option>
      <!-- Diisi melalui AJAX -->
    </select>

    <label for="week">Minggu:</label>
    <select id="week">
      <option value="">Semua</option>
      <?php for ($w=1; $w<=52; $w++) echo "<option value=\"$w\">Minggu $w</option>"; ?>
    </select>
  </div>

  <!-- Jadual Prestasi -->
  <div class="table-container">
    <h3>Senarai Prestasi Pelatih</h3>
    <table id="performanceTable" class="display nowrap" style="width:100%">
      <thead>
      <tr>
        <th>performance_id</th>
        <th>trainee_id</th>
        <th>week</th>
        <th>roll_left_right</th>
        <th>reach_forward</th>
        <th>sit</th>
        <th>crawl</th>
        <th>creep</th>
        <th>kneel</th>
        <th>stand</th>
        <th>climb_stairs</th>
        <th>descend_stairs</th>
        <th>swallow_safely</th>
        <th>wash_hands_before_after</th>
        <th>bring_food_to_mouth</th>
        <th>eat_with_hand</th>
        <th>eat_with_spoon</th>
        <th>drink_with_cup</th>
        <th>clean_utensils</th>
        <th>arrange_utensils</th>
        <th>identify_front_back</th>
        <th>remove_clothing</th>
        <th>put_on_clothes</th>
        <th>put_on_socks_shoes</th>
        <th>button_up</th>
        <th>comb_hair</th>
        <th>apply_powder</th>
        <th>know_when_toilet</th>
        <th>tell_need_toilet</th>
        <th>go_to_toilet</th>
        <th>understand_toilet_use</th>
        <th>clean_after_toilet</th>
        <th>manage_menstruation</th>
        <th>go_to_bathroom</th>
        <th>pour_water_on_body</th>
        <th>soap_and_clean_body</th>
        <th>dry_body_with_towel</th>
        <th>brush_teeth</th>
        <th>put_toothpaste_on_brush</th>
        <th>fold_clothes</th>
        <th>wash_own_dishes</th>
        <th>prepare_drink</th>
        <th>wash_own_clothes</th>
        <th>take_care_siblings</th>
        <th>do_simple_chores</th>
        <th>identify_basic_shapes</th>
        <th>understand_basic_colors</th>
        <th>understand_number_concept</th>
        <th>write_some_numbers</th>
        <th>write_some_letters</th>
        <th>write_some_words</th>
        <th>write_own_name</th>
        <th>know_own_name</th>
        <th>differentiate_big_small</th>
        <th>know_left_right</th>
        <th>know_top_bottom</th>
        <th>express_hungry_full</th>
        <th>understand_right_wrong</th>
        <th>name_body_parts</th>
        <th>tell_gender_when_shown</th>
        <th>understand_family_relation</th>
        <th>name_family_members</th>
        <th>respond_to_greeting</th>
        <th>play_independently</th>
        <th>play_with_friends</th>
        <th>vocalize_during_play</th>
        <th>imitate_during_play</th>
        <th>constructive_block_play</th>
        <th>share_toys_with_friends</th>
        <th>cooperate_in_group_play</th>
        <th>take_turns_in_group_activity</th>
        <th>tidy_up_toys</th>
        <th>say_own_name</th>
        <th>respond_to_name_call</th>
        <th>look_at_speaker</th>
        <th>understand_yes_no</th>
        <th>answer_what_is_this</th>
        <th>say_simple_things</th>
        <th>tell_story_from_picture</th>
        <th>answer_phone_call</th>
        <th>wander_without_purpose</th>
        <th>restless_or_cannot_stay_put</th>
        <th>run_without_reason</th>
        <th>spit_pinch_kick</th>
        <th>passive_no_reaction</th>
        <th>use_vulgar_language</th>
        <th>use_incomprehensible_words</th>
        <th>break_things</th>
        <th>undress_inappropriately</th>
        <th>deliberately_disobey</th>
        <th>sleep_during_day</th>
        <th>age_adjustment_difficulty</th>
        <th>extreme_behaviour</th>
        <th>understand_rules_discipline</th>
        <th>show_affection_to_peers</th>
        <th>argue_when_needs_unmet</th>
        <th>resolve_conflict_peacefully</th>
        <th>worry_when_friends_in_trouble</th>
        <th>express_anger</th>
        <th>use_rude_language</th>
        <th>use_unintelligible_language</th>
        <th>attempt_help_parents_teachers</th>
        <th>pick_up_objects_efficiently</th>
        <th>use_both_hands_well</th>
        <th>stand_jump_on_one_leg</th>
        <th>unscrew_container_lid</th>
        <th>differentiate_by_size</th>
        <th>differentiate_texture</th>
        <th>cut_along_straight_line</th>
        <th>understand_money_up_to_100</th>
        <th>travel_independently_public_transport</th>
        <th>have_savings_account_concept</th>
      </tr>
      </thead>
    </table>
  </div>
</div>

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

<!-- ================== JS Dependencies (susunan penting) ================== -->
<!-- jQuery & DataTables core -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<!-- Buttons dependencies (ikut SUSUNAN ini) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function () {

  // Muatkan senarai pelatih untuk filter Nama
  function loadTraineeList() {
    $.getJSON('data_trainee_performance.php?type=trainee_list', function(rows) {
      const $sel = $('#trainee_perf_id');
      $sel.empty().append('<option value="">Semua</option>');
      (rows || []).forEach(r => {
        $sel.append('<option value="'+ (r.trainee_id ?? '') +'">'+ (r.full_name ?? r.trainee_id) +'</option>');
      });
    });
  }
  loadTraineeList();

  // Util teks penapis & nama fail eksport
  function currentFilterText() {
    const traineeText = $('#trainee_perf_id option:selected').text() || 'Semua';
    const weekText    = $('#week').val() ? ('Minggu ' + $('#week').val()) : 'Semua';
    return `Penapis: Pelatih = ${traineeText} | Minggu = ${weekText}`;
  }
  function exportFilename(prefix) {
    const d=new Date(), pad=n=>String(n).padStart(2,'0');
    const stamp = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}_${pad(d.getHours())}${pad(d.getMinutes())}`;
    const traineeVal = $('#trainee_perf_id').val() || 'all';
    const weekVal    = $('#week').val() || 'all';
    return `${prefix}_pelatih-${traineeVal}_minggu-${weekVal}_${stamp}`;
  }

  // Senarai kolum (pastikan sama dengan kunci JSON dari server)
  const perfColumns = [
    { data:'performance_id', title:'performance_id', defaultContent:'-' },
    { data:'trainee_id', title:'trainee_id', defaultContent:'-' },
    { data:'week', title:'week', defaultContent:'-' },
    { data:'roll_left_right', title:'roll_left_right', defaultContent:'-' },
    { data:'reach_forward', title:'reach_forward', defaultContent:'-' },
    { data:'sit', title:'sit', defaultContent:'-' },
    { data:'crawl', title:'crawl', defaultContent:'-' },
    { data:'creep', title:'creep', defaultContent:'-' },
    { data:'kneel', title:'kneel', defaultContent:'-' },
    { data:'stand', title:'stand', defaultContent:'-' },
    { data:'climb_stairs', title:'climb_stairs', defaultContent:'-' },
    { data:'descend_stairs', title:'descend_stairs', defaultContent:'-' },
    { data:'swallow_safely', title:'swallow_safely', defaultContent:'-' },
    { data:'wash_hands_before_after', title:'wash_hands_before_after', defaultContent:'-' },
    { data:'bring_food_to_mouth', title:'bring_food_to_mouth', defaultContent:'-' },
    { data:'eat_with_hand', title:'eat_with_hand', defaultContent:'-' },
    { data:'eat_with_spoon', title:'eat_with_spoon', defaultContent:'-' },
    { data:'drink_with_cup', title:'drink_with_cup', defaultContent:'-' },
    { data:'clean_utensils', title:'clean_utensils', defaultContent:'-' },
    { data:'arrange_utensils', title:'arrange_utensils', defaultContent:'-' },
    { data:'identify_front_back', title:'identify_front_back', defaultContent:'-' },
    { data:'remove_clothing', title:'remove_clothing', defaultContent:'-' },
    { data:'put_on_clothes', title:'put_on_clothes', defaultContent:'-' },
    { data:'put_on_socks_shoes', title:'put_on_socks_shoes', defaultContent:'-' },
    { data:'button_up', title:'button_up', defaultContent:'-' },
    { data:'comb_hair', title:'comb_hair', defaultContent:'-' },
    { data:'apply_powder', title:'apply_powder', defaultContent:'-' },
    { data:'know_when_toilet', title:'know_when_toilet', defaultContent:'-' },
    { data:'tell_need_toilet', title:'tell_need_toilet', defaultContent:'-' },
    { data:'go_to_toilet', title:'go_to_toilet', defaultContent:'-' },
    { data:'understand_toilet_use', title:'understand_toilet_use', defaultContent:'-' },
    { data:'clean_after_toilet', title:'clean_after_toilet', defaultContent:'-' },
    { data:'manage_menstruation', title:'manage_menstruation', defaultContent:'-' },
    { data:'go_to_bathroom', title:'go_to_bathroom', defaultContent:'-' },
    { data:'pour_water_on_body', title:'pour_water_on_body', defaultContent:'-' },
    { data:'soap_and_clean_body', title:'soap_and_clean_body', defaultContent:'-' },
    { data:'dry_body_with_towel', title:'dry_body_with_towel', defaultContent:'-' },
    { data:'brush_teeth', title:'brush_teeth', defaultContent:'-' },
    { data:'put_toothpaste_on_brush', title:'put_toothpaste_on_brush', defaultContent:'-' },
    { data:'fold_clothes', title:'fold_clothes', defaultContent:'-' },
    { data:'wash_own_dishes', title:'wash_own_dishes', defaultContent:'-' },
    { data:'prepare_drink', title:'prepare_drink', defaultContent:'-' },
    { data:'wash_own_clothes', title:'wash_own_clothes', defaultContent:'-' },
    { data:'take_care_siblings', title:'take_care_siblings', defaultContent:'-' },
    { data:'do_simple_chores', title:'do_simple_chores', defaultContent:'-' },
    { data:'identify_basic_shapes', title:'identify_basic_shapes', defaultContent:'-' },
    { data:'understand_basic_colors', title:'understand_basic_colors', defaultContent:'-' },
    { data:'understand_number_concept', title:'understand_number_concept', defaultContent:'-' },
    { data:'write_some_numbers', title:'write_some_numbers', defaultContent:'-' },
    { data:'write_some_letters', title:'write_some_letters', defaultContent:'-' },
    { data:'write_some_words', title:'write_some_words', defaultContent:'-' },
    { data:'write_own_name', title:'write_own_name', defaultContent:'-' },
    { data:'know_own_name', title:'know_own_name', defaultContent:'-' },
    { data:'differentiate_big_small', title:'differentiate_big_small', defaultContent:'-' },
    { data:'know_left_right', title:'know_left_right', defaultContent:'-' },
    { data:'know_top_bottom', title:'know_top_bottom', defaultContent:'-' },
    { data:'express_hungry_full', title:'express_hungry_full', defaultContent:'-' },
    { data:'understand_right_wrong', title:'understand_right_wrong', defaultContent:'-' },
    { data:'name_body_parts', title:'name_body_parts', defaultContent:'-' },
    { data:'tell_gender_when_shown', title:'tell_gender_when_shown', defaultContent:'-' },
    { data:'understand_family_relation', title:'understand_family_relation', defaultContent:'-' },
    { data:'name_family_members', title:'name_family_members', defaultContent:'-' },
    { data:'respond_to_greeting', title:'respond_to_greeting', defaultContent:'-' },
    { data:'play_independently', title:'play_independently', defaultContent:'-' },
    { data:'play_with_friends', title:'play_with_friends', defaultContent:'-' },
    { data:'vocalize_during_play', title:'vocalize_during_play', defaultContent:'-' },
    { data:'imitate_during_play', title:'imitate_during_play', defaultContent:'-' },
    { data:'constructive_block_play', title:'constructive_block_play', defaultContent:'-' },
    { data:'share_toys_with_friends', title:'share_toys_with_friends', defaultContent:'-' },
    { data:'cooperate_in_group_play', title:'cooperate_in_group_play', defaultContent:'-' },
    { data:'take_turns_in_group_activity', title:'take_turns_in_group_activity', defaultContent:'-' },
    { data:'tidy_up_toys', title:'tidy_up_toys', defaultContent:'-' },
    { data:'say_own_name', title:'say_own_name', defaultContent:'-' },
    { data:'respond_to_name_call', title:'respond_to_name_call', defaultContent:'-' },
    { data:'look_at_speaker', title:'look_at_speaker', defaultContent:'-' },
    { data:'understand_yes_no', title:'understand_yes_no', defaultContent:'-' },
    { data:'answer_what_is_this', title:'answer_what_is_this', defaultContent:'-' },
    { data:'say_simple_things', title:'say_simple_things', defaultContent:'-' },
    { data:'tell_story_from_picture', title:'tell_story_from_picture', defaultContent:'-' },
    { data:'answer_phone_call', title:'answer_phone_call', defaultContent:'-' },
    { data:'wander_without_purpose', title:'wander_without_purpose', defaultContent:'-' },
    { data:'restless_or_cannot_stay_put', title:'restless_or_cannot_stay_put', defaultContent:'-' },
    { data:'run_without_reason', title:'run_without_reason', defaultContent:'-' },
    { data:'spit_pinch_kick', title:'spit_pinch_kick', defaultContent:'-' },
    { data:'passive_no_reaction', title:'passive_no_reaction', defaultContent:'-' },
    { data:'use_vulgar_language', title:'use_vulgar_language', defaultContent:'-' },
    { data:'use_incomprehensible_words', title:'use_incomprehensible_words', defaultContent:'-' },
    { data:'break_things', title:'break_things', defaultContent:'-' },
    { data:'undress_inappropriately', title:'undress_inappropriately', defaultContent:'-' },
    { data:'deliberately_disobey', title:'deliberately_disobey', defaultContent:'-' },
    { data:'sleep_during_day', title:'sleep_during_day', defaultContent:'-' },
    { data:'age_adjustment_difficulty', title:'age_adjustment_difficulty', defaultContent:'-' },
    { data:'extreme_behaviour', title:'extreme_behaviour', defaultContent:'-' },
    { data:'understand_rules_discipline', title:'understand_rules_discipline', defaultContent:'-' },
    { data:'show_affection_to_peers', title:'show_affection_to_peers', defaultContent:'-' },
    { data:'argue_when_needs_unmet', title:'argue_when_needs_unmet', defaultContent:'-' },
    { data:'resolve_conflict_peacefully', title:'resolve_conflict_peacefully', defaultContent:'-' },
    { data:'worry_when_friends_in_trouble', title:'worry_when_friends_in_trouble', defaultContent:'-' },
    { data:'express_anger', title:'express_anger', defaultContent:'-' },
    { data:'use_rude_language', title:'use_rude_language', defaultContent:'-' },
    { data:'use_unintelligible_language', title:'use_unintelligible_language', defaultContent:'-' },
    { data:'attempt_help_parents_teachers', title:'attempt_help_parents_teachers', defaultContent:'-' },
    { data:'pick_up_objects_efficiently', title:'pick_up_objects_efficiently', defaultContent:'-' },
    { data:'use_both_hands_well', title:'use_both_hands_well', defaultContent:'-' },
    { data:'stand_jump_on_one_leg', title:'stand_jump_on_one_leg', defaultContent:'-' },
    { data:'unscrew_container_lid', title:'unscrew_container_lid', defaultContent:'-' },
    { data:'differentiate_by_size', title:'differentiate_by_size', defaultContent:'-' },
    { data:'differentiate_texture', title:'differentiate_texture', defaultContent:'-' },
    { data:'cut_along_straight_line', title:'cut_along_straight_line', defaultContent:'-' },
    { data:'understand_money_up_to_100', title:'understand_money_up_to_100', defaultContent:'-' },
    { data:'travel_independently_public_transport', title:'travel_independently_public_transport', defaultContent:'-' },
    { data:'have_savings_account_concept', title:'have_savings_account_concept', defaultContent:'-' }
  ];

  const perfTable = $('#performanceTable').DataTable({
    ajax: {
      url: 'data_trainee_performance.php?type=table',
      data: function (d) {
        d.trainee_id = $('#trainee_perf_id').val() || '';
        d.week       = $('#week').val() || '';
      },
      dataSrc: function(json) {
        // console.log('DEBUG JSON', json);
        return json.data || [];
      }
    },
    columns: perfColumns,
    scrollX: true,              // penting kerana kolum banyak
    // scrollY: '60vh',         // boleh aktifkan jika mahu ketinggian tetap
    // scroller: true,          // guna plugin Scroller jika dataset besar
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    dom: 'Bfrtip',
    buttons: [
      {
        extend: 'excelHtml5',
        text: 'Excel',
        title: 'Laporan Prestasi Pelatih',
        filename: function(){ return exportFilename('laporan_prestasi_pelatih'); },
        exportOptions: { columns: ':visible' },
        messageTop: function(){ return currentFilterText(); }
      },
      {
        extend: 'pdfHtml5',
        text: 'PDF',
        title: 'Laporan Prestasi Pelatih',
        filename: function(){ return exportFilename('laporan_prestasi_pelatih'); },
        exportOptions: { columns: ':visible' },
        orientation: 'landscape',
        pageSize: 'A3', // disyorkan untuk banyak kolum
        customize: function (doc) {
          doc.pageMargins = [24, 24, 24, 24];
          doc.defaultStyle.fontSize = 8;
          doc.styles.tableHeader.fontSize = 9;
          const filterLine = { text: currentFilterText(), margin: [0, 0, 0, 10] };
          doc.content.splice(1, 0, filterLine);
        }
      },
      {
        extend: 'print',
        text: 'Cetak',
        title: 'Laporan Prestasi Pelatih',
        exportOptions: { columns: ':visible' },
        customize: function (win) {
          const p = win.document.createElement('p');
          p.textContent = currentFilterText();
          p.style.margin = '0 0 12px 0';
          const h1 = win.document.querySelector('h1');
          if (h1 && h1.parentNode) h1.parentNode.insertBefore(p, h1.nextSibling);
          const tbl = win.document.querySelector('table');
          if (tbl) tbl.style.fontSize = '10px';
        }
      }
    ],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/ms-MY.json' }
  });

  // Reload bila filter berubah
  $('#trainee_perf_id, #week').on('change', function(){
    perfTable.ajax.reload();
  });

});
</script>
</body>
</html>
