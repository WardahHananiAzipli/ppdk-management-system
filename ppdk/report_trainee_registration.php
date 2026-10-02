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

$profilePic = !empty($data['profile_pic']) ? 'uploads/' . $data['profile_pic'] : 'img/profile-placeholder.jpg';
$updateSuccess = isset($_GET['update']) && $_GET['update'] === 'success';
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendaftaran Pelatih</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/scroll.css">
    <link rel="stylesheet" href="css/report.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <!-- CSS Buttons -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

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

    <!-- Direct links -->
    <a href="report_trainee_allowance.php">Elaun Pelatih</a>
    <a href="report_trainee_performance.php">Laporan Kemajuan Pelatih</a>

  </div>
  </div>
</div>

<!-- Main Body -->
<div class="main-container">
<h2>Laporan Pendaftaran Pelatih</h2>

<!-- Filter -->
<div class="filter-container">
    <label for="bulan">Bulan:</label>
    <select id="bulan">
        <option value="">Semua</option>
        <?php
        // Senarai bulan dalam Bahasa Melayu
        $bulan_ms = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Mac',
            4  => 'April',
            5  => 'Mei',
            6  => 'Jun',
            7  => 'Julai',
            8  => 'Ogos',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Disember'
        ];

        // Kekalkan value 1..12 seperti sebelum ini
        foreach ($bulan_ms as $num => $name) {
            echo "<option value=\"$num\">$name</option>";
        }
        ?>
    </select>

    <label for="tahun">Tahun:</label>
    <select id="tahun">
        <option value="">Semua</option>
        <?php for ($y = date('Y'); $y >= 2000; $y--) echo "<option value='$y'>$y</option>"; ?>
    </select>

    <label for="jantina">Jantina:</label>
    <select id="jantina">
        <option value="">Semua</option>
        <option value="Lelaki">Lelaki</option>
        <option value="Perempuan">Perempuan</option>
    </select>
</div>

<div class="total-row">
    <!-- Card -->
    <div class="chart-container" style="width: 30%; display: flex; justify-content: center; align-items: center; height: 300px; flex-direction: column;">
        <h3 class="chart-title">Ringkasan Pendaftaran Pelatih</h3>
        <div id="totalInfo"></div>
    </div>

    <!-- Pie chart -->
    <div class="chart-container" style="width: 30%; display: flex; justify-content: center; align-items: center; height: 300px;">
        <canvas id="chartByGender"></canvas>
    </div>
</div>

<div class="chart-row">
    <!-- Bar chart (month) -->
    <div class="chart-container" style="width: 40%; display: flex; justify-content: center; align-items: center; height: 300px;">
        <canvas id="chartByMonth"></canvas>
    </div>
    
    <!-- Bar chart (year) -->
    <div class="chart-container" style="width: 40%; display: flex; justify-content: center; align-items: center; height: 300px;">
        <canvas id="chartByYear"></canvas>
    </div>
</div>

<!-- Table -->
<div class="table-container">
    <h3>Senarai Pendaftaran Pelatih</h3>
    <table id="staffTable" class="display" style="width:100%">
        <thead>
        <tr>
            <th>ID Pelatih</th>
            <th>Nama Pelatih</th>
            <th>Jantina</th>
            <th>Tarikh Pendaftaran</th>
        </tr>
        </thead>
    </table>
</div>

<!-- 1) jQuery & DataTables core -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<!-- 2) Dependencies untuk eksport -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- 3) (Opsyen) Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function () {
    let chartMonth, chartYear, chartGender;

        const mapMs = {
        'January':'Januari','February':'Februari','March':'Mac','April':'April','May':'Mei','June':'Jun',
        'July':'Julai','August':'Ogos','September':'September','October':'Oktober','November':'November','December':'Disember'
        };
        const bulanFromNum = ['','Januari','Februari','Mac','April','Mei','Jun','Julai','Ogos','September','Oktober','November','Disember'];


            // (TAMBAH) Peta tajuk untuk setiap jenis carta
            const chartTitles = {
                month: 'Pendaftaran Mengikut Bulan',
                year: 'Pendaftaran Mengikut Tahun',
                gender: 'Pendaftaran Mengikut Jantina'
            };

        // (TAMBAH) Util untuk bina options dengan tajuk (kekal responsive:false ikut gaya asal)
        function buildOptions(type) {
            const isDoughnut = (type === 'gender');
            return {
                responsive: false,
                animation: { duration: 0 },
                plugins: {
                    title: {
                        display: true,
                        text: chartTitles[type] || '',
                        font: { size: 16, weight: '600' },
                        padding: { top: 6, bottom: 10 }
                    },
                    // Tunjuk legend untuk donut sahaja, bar lebih kemas tanpa legend
                    legend: { display: isDoughnut, position: 'bottom', labels: { boxWidth: 12 } },
                    tooltip: { enabled: true }
                },
                // Skala hanya untuk bar
                scales: isDoughnut ? {} : {
                    x: { ticks: { autoSkip: true, maxRotation: 0 }, grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: '#eee' } }
                }
            };
        }

    function loadChart(id, type, chartType, colors, chartRef) {
        const bulan = $('#bulan').val();
        const tahun = $('#tahun').val();
        const jantina = $('#jantina').val();

        $.getJSON(`data_trainee_registration.php?type=${type}&bulan=${bulan}&tahun=${tahun}&jantina=${jantina}`, function (data) {
            let labels = data.map(d => d.label);
            const values = data.map(d => d.value);

            if (type === 'month') {
                labels = labels.map(l => {
                    // jika server hantar nombor 1..12
                    const n = parseInt(l, 10);
                    if (!isNaN(n) && n >= 1 && n <= 12) return bulanFromNum[n];
                    // jika server hantar nama Inggeris
                    return mapMs[l] ?? l;
                });
            }

            if (window[chartRef]) {
                window[chartRef].destroy();
            }

            window[chartRef] = new Chart(document.getElementById(id), {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah',
                        data: values,
                        backgroundColor: colors,
                    }]
                },
                options: buildOptions(type)
            });
        });
    }

    function loadTotalInfo() {
        const bulan = $('#bulan').val();
        const tahun = $('#tahun').val();
        const jantina = $('#jantina').val();

        $.getJSON(`data_trainee_registration.php?type=total&bulan=${bulan}&tahun=${tahun}&jantina=${jantina}`, function (data) {
            $('#totalInfo').html(`
                <h4>Jumlah Keseluruhan: ${data.total}</h4>
                <p>Lelaki: ${data.male}</p>
                <p>Perempuan: ${data.female}</p>
            `);
        });
    }

    function filterAndUpdateCharts() {
        loadChart('chartByMonth', 'month', 'bar', '#4e73df', 'chartMonth');
        loadChart('chartByYear', 'year', 'bar', '#1cc88a', 'chartYear');
        loadChart('chartByGender', 'gender', 'doughnut', ['#36b9cc', '#f6c23e'], 'chartGender');
        loadTotalInfo();
    }

    filterAndUpdateCharts();

    // Util: teks penapis semasa (untuk tajuk eksport & messageTop)
    function currentFilterText() {
    const bulanText = $('#bulan option:selected').text() || 'Semua';
    const tahunText = $('#tahun option:selected').text() || 'Semua';
    const jantinaText = $('#jantina option:selected').text() || 'Semua';
    return `Penapis: Bulan = ${bulanText} | Tahun = ${tahunText} | Jantina = ${jantinaText}`;
    }

    // Util: nama fail ikut tarikh/masa + penapis ringkas
    function exportFilename(prefix) {
    const d = new Date();
    const pad = n => String(n).padStart(2, '0');
    const stamp = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}_${pad(d.getHours())}${pad(d.getMinutes())}`;
    const bulanVal = $('#bulan').val() || 'all';
    const tahunVal = $('#tahun').val() || 'all';
    const jantinaVal = $('#jantina').val() || 'all';
    return `${prefix}_bulan-${bulanVal}_tahun-${tahunVal}_jantina-${jantinaVal}_${stamp}`;
    }

    const table = $('#staffTable').DataTable({
    ajax: {
        url: 'data_trainee_registration.php?type=table',
        data: function (d) {
        d.bulan = $('#bulan').val();
        d.tahun = $('#tahun').val();
        d.jantina = $('#jantina').val();
        },
        dataSrc: 'data'
    },
    columns: [
        { data: 'trainee_id', title: 'ID Pelatih' },
        { data: 'full_name', title: 'Nama Pelatih' },
        { data: 'gender', title: 'Jantina' },
        { data: 'registration_date', title: 'Tarikh Pendaftaran' }
    ],
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],

    // Aktifkan butang eksport
    dom: 'Bfrtip',
    buttons: [
        {
        extend: 'excelHtml5',
        text: 'Excel',
        title: 'Laporan Pendaftaran Kakitangan',
        filename: function () { return exportFilename('laporan_pendaftaran_kakitangan'); },
        exportOptions: { columns: ':visible' },
        messageTop: function () { return currentFilterText(); }
        },
        {
        extend: 'pdfHtml5',
        text: 'PDF',
        title: 'Laporan Pendaftaran Kakitangan',
        filename: function () { return exportFilename('laporan_pendaftaran_kakitangan'); },
        exportOptions: { columns: ':visible' },
        orientation: 'landscape',
        pageSize: 'A4',
        customize: function (doc) {
            doc.pageMargins = [36, 36, 36, 36];
            doc.defaultStyle.fontSize = 10;
            doc.styles.tableHeader.fontSize = 11;
            const filterLine = { text: currentFilterText(), margin: [0, 0, 0, 10] };
            doc.content.splice(1, 0, filterLine);
            doc.footer = function(currentPage, pageCount) {
            return {
                columns: [
                { text: 'PPDK INSANI', alignment: 'left', margin: [36, 0] },
                { text: currentPage + ' / ' + pageCount, alignment: 'right', margin: [0, 0, 36, 0] }
                ]
            };
            };
        }
        },
        {
        extend: 'print',
        text: 'Cetak',
        title: 'Laporan Pendaftaran Kakitangan',
        exportOptions: { columns: ':visible' },
        customize: function (win) {
            const p = win.document.createElement('p');
            p.textContent = currentFilterText();
            p.style.marginTop = '0';
            p.style.marginBottom = '12px';
            const h1 = win.document.querySelector('h1');
            if (h1 && h1.parentNode) h1.parentNode.insertBefore(p, h1.nextSibling);
            const tbl = win.document.querySelector('table');
            if (tbl) tbl.style.fontSize = '12px';
        }
        }
    ],
    language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/ms-MY.json'
    }
    });

    // Kekal: reload bila penapis berubah
    $('#bulan, #tahun, #jantina').on('change', function () {
    table.ajax.reload();
    filterAndUpdateCharts();
    });
});
</script>

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
