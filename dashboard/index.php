
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/functions.php';

require_login();

// ========================================
// DATA PENGGUNA
// ========================================

$id_user = (int) ($_SESSION['id'] ?? 0);
$nama_user = $_SESSION['nama'] ?? 'Pengguna';

if ($id_user <= 0) {
    header("Location: ../auth/login.php");
    exit;
}

// ========================================
// MENGHITUNG TOTAL PROYEK
// ========================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) FROM projects WHERE user_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $total_project);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// ========================================
// MENGHITUNG PROYEK DESAIN
// ========================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) FROM projects
     WHERE user_id = ? AND jenis = 'design'"
);

mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $total_design);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// ========================================
// MENGHITUNG PROYEK KODE
// ========================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) FROM projects
     WHERE user_id = ? AND jenis = 'code'"
);

mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $total_code);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// ========================================
// MENGAMBIL PROYEK TERBARU
// ========================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama_project, jenis, status, created_at
     FROM projects
     WHERE user_id = ?
     ORDER BY created_at DESC, id DESC
     LIMIT 5"
);

if (!$stmt) {
    die("Query proyek gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $project_id,
    $project_name,
    $project_type,
    $project_status,
    $project_date
);

$projects = [];

while (mysqli_stmt_fetch($stmt)) {
    $projects[] = [
        'id'      => (int) $project_id,
        'nama'    => $project_name,
        'jenis'   => $project_type,
        'status'  => $project_status,
        'tanggal' => $project_date
    ];
}

mysqli_stmt_close($stmt);

// ========================================
// URL DESIGN STUDIO
// ========================================

// Ambil proyek terbaru milik pengguna.
// Jika belum memiliki proyek, arahkan ke halaman proyek.

if (!empty($projects)) {
    $studio_url = "../studio/design.php?project_id=" .
                  (int) $projects[0]['id'];
} else {
    $studio_url = "../projects/index.php";
}

// ========================================
// INISIAL PENGGUNA
// ========================================

$inisial = strtoupper(substr($nama_user, 0, 1));

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard | VISUALOGIC</title>

    <link rel="stylesheet"
          href="../assets/css/dashboard.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="dashboard-wrapper">

    <!-- ==================================
         SIDEBAR
    =================================== -->

    <aside class="sidebar" id="sidebar">

        <div class="brand">

            <div class="brand-icon">
                <i class="fa-solid fa-cubes"></i>
            </div>

            <div>
                <h2>VISUALOGIC</h2>
                <small>DESIGN MEETS CODE</small>
            </div>

        </div>

        <div class="menu-label">MENU UTAMA</div>

        <nav class="sidebar-menu">

            <a href="index.php" class="active">
                <i class="fa-solid fa-house"></i>
                Dashboard
            </a>

            <a href="../projects/index.php">
                <i class="fa-solid fa-folder-open"></i>
                My Projects
            </a>

        </nav>

        <div class="menu-label">STUDIO</div>

        <nav class="sidebar-menu">

            <a href="<?= e($studio_url); ?>">
                <i class="fa-solid fa-pen-ruler"></i>
                Design Studio
            </a>

            <a href="../studio/code.php">
                <i class="fa-solid fa-code"></i>
                Code Studio
            </a>

        </nav>

        <div class="menu-label">PENGATURAN</div>

        <nav class="sidebar-menu">

            <a href="#">
                <i class="fa-solid fa-gear"></i>
                Pengaturan
            </a>

        </nav>

        <!-- PROFIL PENGGUNA -->

        <div class="sidebar-bottom">

            <div class="user-mini">

                <div class="user-avatar">
                    <?= e($inisial); ?>
                </div>

                <div>
                    <strong><?= e($nama_user); ?></strong>
                    <small>Pengguna VISUALOGIC</small>
                </div>

            </div>

            <form action="../auth/logout.php" method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()); ?>"
                >

                <button type="submit" class="logout-button">

                    <i class="fa-solid fa-right-from-bracket"></i>

                    Keluar dari akun

                </button>

            </form>

        </div>

    </aside>


    <!-- ==================================
         KONTEN UTAMA
    =================================== -->

    <main class="main-content">


        <!-- NAVBAR -->

        <header class="topbar">

            <div style="display:flex; align-items:center; gap:14px;">

                <button
                    class="mobile-menu"
                    id="mobileMenu"
                    aria-label="Buka menu"
                >
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div class="page-heading">

                    <h1>Dashboard</h1>

                    <p>
                        Kelola kreativitas dan proyek digital Anda.
                    </p>

                </div>

            </div>

            <div class="topbar-right">

                <div class="topbar-date">

                    <i class="fa-regular fa-calendar"></i>

                    <?= date('d F Y'); ?>

                </div>

            </div>

        </header>


        <!-- ==================================
             WELCOME BANNER
        =================================== -->

        <section class="welcome-banner">

            <div class="welcome-text">

                <small>WELCOME TO VISUALOGIC</small>

                <h2>
                    Halo, <?= e($nama_user); ?>!
                </h2>

                <p>
                    Saatnya mengubah ide kreatif menjadi karya digital.
                    Mulai desain visual, tulis kode, dan kembangkan
                    proyek Anda dalam satu tempat.
                </p>

            </div>

            <div class="welcome-actions">

                <a href="../projects/tambah.php" class="btn-light">

                    <i class="fa-solid fa-plus"></i>

                    Buat Proyek

                </a>

                <a href="<?= e($studio_url); ?>"
                   class="btn-outline-light">

                    <i class="fa-solid fa-pen-ruler"></i>

                    Mulai Mendesain

                </a>

            </div>

        </section>


        <!-- ==================================
             STATISTIK
        =================================== -->

        <div class="section-title">

            <h3>Ringkasan Proyek</h3>

        </div>

        <section class="stats-grid">

            <div class="stat-card">

                <div class="stat-icon total">

                    <i class="fa-solid fa-folder"></i>

                </div>

                <div class="stat-info">

                    <small>Total Proyek</small>

                    <h2><?= (int) $total_project; ?></h2>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon design">

                    <i class="fa-solid fa-pen-ruler"></i>

                </div>

                <div class="stat-info">

                    <small>Proyek Desain</small>

                    <h2><?= (int) $total_design; ?></h2>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon code">

                    <i class="fa-solid fa-code"></i>

                </div>

                <div class="stat-info">

                    <small>Proyek Kode</small>

                    <h2><?= (int) $total_code; ?></h2>

                </div>

            </div>

        </section>


        <!-- ==================================
             AKSES CEPAT
        =================================== -->

        <div class="section-title">

            <h3>Mulai Berkarya</h3>

        </div>

        <section class="quick-grid">


            <!-- DESIGN STUDIO -->

            <a href="<?= e($studio_url); ?>" class="quick-card">

                <div class="quick-icon">

                    <i class="fa-solid fa-palette"></i>

                </div>

                <h4>Design Studio</h4>

                <p>
                    Buat desain visual, atur elemen,
                    warna, teks, dan komposisi canvas.
                </p>

                <span class="quick-link">

                    Buka Design Studio

                    <i class="fa-solid fa-arrow-right"></i>

                </span>

            </a>


            <!-- CODE STUDIO -->

            <a href="../studio/code.php" class="quick-card">

                <div class="quick-icon">

                    <i class="fa-solid fa-laptop-code"></i>

                </div>

                <h4>Code Studio</h4>

                <p>
                    Tulis dan kembangkan HTML, CSS,
                    serta JavaScript dengan editor kode.
                </p>

                <span class="quick-link">

                    Buka Code Studio

                    <i class="fa-solid fa-arrow-right"></i>

                </span>

            </a>


        </section>


        <!-- ==================================
             PROYEK TERBARU
        =================================== -->

        <div class="section-title">

            <h3>Proyek Terbaru</h3>

            <a href="../projects/index.php">

                Lihat Semua

                <i class="fa-solid fa-arrow-right"></i>

            </a>

        </div>


        <section class="project-table-wrap">

            <?php if (!empty($projects)): ?>

                <table class="project-table">

                    <thead>

                        <tr>

                            <th>Nama Proyek</th>

                            <th>Jenis</th>

                            <th>Status</th>

                            <th>Tanggal Dibuat</th>

                            <th>Aksi</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($projects as $project): ?>

                            <tr>

                                <!-- NAMA PROYEK -->

                                <td>

                                    <span class="project-name">

                                        <?= e($project['nama']); ?>

                                    </span>

                                </td>


                                <!-- JENIS PROYEK -->

                                <td>

                                    <span class="project-type <?= e($project['jenis']); ?>">

                                        <?= e(ucfirst($project['jenis'])); ?>

                                    </span>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <?= e(ucfirst($project['status'])); ?>

                                </td>


                                <!-- TANGGAL -->

                                <td>

                                    <?php
                                    $timestamp = strtotime($project['tanggal']);
                                    echo $timestamp
                                        ? date('d-m-Y', $timestamp)
                                        : '-';
                                    ?>

                                </td>


                                <!-- AKSI DESIGN STUDIO -->

                                <td>

                                    <a
                                        href="../studio/design.php?project_id=<?= (int) $project['id']; ?>"
                                        style="
                                            display:inline-block;
                                            background:#176b87;
                                            color:white;
                                            padding:9px 12px;
                                            border-radius:7px;
                                            text-decoration:none;
                                            font-size:12px;
                                            font-weight:600;
                                            white-space:nowrap;
                                        "
                                    >

                                        <i class="fa-solid fa-palette"></i>

                                        Buka Studio

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="empty-state">

                    <i class="fa-regular fa-folder-open"></i>

                    <p>
                        Belum ada proyek yang dibuat.
                    </p>

                    <a href="../projects/tambah.php">

                        <i class="fa-solid fa-plus"></i>

                        Buat proyek pertama

                    </a>

                </div>

            <?php endif; ?>

        </section>


        <!-- ==================================
             FOOTER
        =================================== -->

        <footer class="dashboard-footer">

            © <?= date('Y'); ?> VISUALOGIC.

            Design Beyond Pixels —

            Where Visual Creativity Meets Code Intelligence.

        </footer>


    </main>

</div>


<!-- ==================================
     JAVASCRIPT MENU RESPONSIVE
=================================== -->

<script>

const mobileMenu = document.getElementById('mobileMenu');

const sidebar = document.getElementById('sidebar');

if (mobileMenu && sidebar) {

    mobileMenu.addEventListener('click', function () {

        sidebar.classList.toggle('show');

    });


    document.addEventListener('click', function (event) {

        if (
            window.innerWidth <= 900 &&
            !sidebar.contains(event.target) &&
            !mobileMenu.contains(event.target)
        ) {

            sidebar.classList.remove('show');

        }

    });

}

</script>

</body>
</html>
