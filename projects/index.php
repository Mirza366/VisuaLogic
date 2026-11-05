
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan pengguna sudah login
require_login();

// Ambil ID pengguna dari session
$user_id = (int) ($_SESSION['id'] ?? 0);

if ($user_id <= 0) {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil seluruh proyek milik pengguna yang sedang login
$query = "SELECT id, nama_project, deskripsi, jenis, status, created_at
          FROM projects
          WHERE user_id = ?
          ORDER BY id DESC";

$stmt = mysqli_prepare($koneksi, $query);

if (!$stmt) {
    die("Gagal menyiapkan query: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$projects = [];

while ($row = mysqli_fetch_assoc($result)) {
    $projects[] = $row;
}

mysqli_stmt_close($stmt);

// Fungsi untuk mengamankan output HTML
function escape_output($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Proyek Saya | VISUALOGIC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f7fa;
            color: #243b53;
            font-family: 'Inter', sans-serif;
        }

        .navbar {
            background: #102a43;
            padding: 17px 0;
        }

        .brand {
            color: white;
            font-size: 21px;
            font-weight: 800;
            text-decoration: none;
            letter-spacing: .5px;
        }

        .brand span {
            color: #35c6bd;
        }

        .nav-link {
            color: #d9eaf0 !important;
            font-size: 14px;
            font-weight: 600;
        }

        .main-container {
            max-width: 1200px;
            margin: auto;
            padding: 40px 20px;
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .page-heading h1 {
            font-size: 29px;
            font-weight: 800;
            margin-bottom: 8px;
            color: #102a43;
        }

        .page-heading p {
            color: #627d98;
            margin: 0;
            font-size: 14px;
        }

        .btn-add {
            background: #176b87;
            color: white;
            padding: 12px 19px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: .2s;
        }

        .btn-add:hover {
            background: #102a43;
            color: white;
            transform: translateY(-2px);
        }

        .project-count {
            color: #176b87;
            font-weight: 700;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .project-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 22px;
        }

        .project-card {
            background: white;
            border: 1px solid #d9e2ec;
            border-radius: 16px;
            overflow: hidden;
            transition: .25s;
        }

        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(16, 42, 67, .09);
        }

        .project-cover {
            height: 145px;
            background: linear-gradient(135deg, #102a43, #176b87);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 45px;
        }

        .project-content {
            padding: 20px;
        }

        .project-content h3 {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 9px;
            color: #102a43;
            overflow-wrap: anywhere;
        }

        .project-description {
            color: #627d98;
            font-size: 13px;
            line-height: 1.6;
            min-height: 42px;
            overflow-wrap: anywhere;
        }

        .project-meta {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin: 15px 0;
            align-items: center;
        }

        .project-type,
        .project-status {
            font-size: 11px;
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .project-type {
            color: #176b87;
            background: #d9eef3;
        }

        .project-status {
            color: #237a57;
            background: #e2f5eb;
        }

        .project-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-studio {
            flex: 1;
            min-width: 130px;
            text-align: center;
            background: #176b87;
            color: white;
            padding: 11px 12px;
            border-radius: 9px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
        }

        .btn-studio:hover {
            background: #102a43;
            color: white;
        }

        .btn-edit {
            background: #edf2f7;
            color: #334e68;
            padding: 11px 13px;
            border-radius: 9px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
        }

        .btn-edit:hover {
            background: #d9e2ec;
            color: #102a43;
        }

        .empty-state {
            background: white;
            border: 1px dashed #bcccdc;
            padding: 55px 20px;
            text-align: center;
            border-radius: 16px;
        }

        .empty-state i {
            font-size: 48px;
            color: #9fb3c8;
            margin-bottom: 18px;
        }

        .empty-state h3 {
            font-size: 20px;
            font-weight: 800;
        }

        .empty-state p {
            color: #627d98;
            font-size: 14px;
        }

        footer {
            text-align: center;
            padding: 25px 15px;
            color: #829ab1;
            font-size: 12px;
        }

        @media (max-width: 576px) {
            .main-container {
                padding: 25px 15px;
            }

            .page-heading h1 {
                font-size: 24px;
            }

            .btn-add {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="../dashboard/index.php" class="brand">
            VISUAL<span>OGIC</span>
        </a>

        <div class="d-flex gap-3">
            <a href="../dashboard/index.php" class="nav-link">
                <i class="fas fa-home"></i> Dashboard
            </a>

            <a href="../auth/logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </div>
</nav>

<main class="main-container">

    <div class="page-heading">
        <div>
            <h1>Proyek Saya</h1>
            <p>Kelola desain dan proyek kreatif kamu di VISUALOGIC.</p>
        </div>

        <a href="tambah.php" class="btn-add">
            <i class="fas fa-plus"></i> Buat Proyek Baru
        </a>
    </div>

    <div class="project-count">
        <i class="fas fa-folder-open"></i>
        Total proyek: <?= count($projects); ?>
    </div>

    <?php if (count($projects) > 0): ?>

        <div class="project-grid">

            <?php foreach ($projects as $project): ?>

                <?php
                    $project_id = (int) $project['id'];
                    $nama_project = escape_output($project['nama_project']);
                    $deskripsi = escape_output(
                        $project['deskripsi'] ?: 'Belum ada deskripsi proyek.'
                    );

                    $jenis = escape_output($project['jenis']);
                    $status = escape_output($project['status']);

                    // URL Design Studio selalu membawa ID proyek
                    $studio_url = "../studio/design.php?project_id=" . $project_id;
                ?>

                <div class="project-card">

                    <div class="project-cover">
                        <?php if ($project['jenis'] === 'code'): ?>
                            <i class="fas fa-code"></i>
                        <?php elseif ($project['jenis'] === 'hybrid'): ?>
                            <i class="fas fa-layer-group"></i>
                        <?php else: ?>
                            <i class="fas fa-palette"></i>
                        <?php endif; ?>
                    </div>

                    <div class="project-content">

                        <h3><?= $nama_project; ?></h3>

                        <div class="project-description">
                            <?= $deskripsi; ?>
                        </div>

                        <div class="project-meta">
                            <span class="project-type">
                                <?= $jenis; ?>
                            </span>

                            <span class="project-status">
                                <?= $status; ?>
                            </span>
                        </div>

                        <div class="project-actions">

                            <a
                                href="<?= escape_output($studio_url); ?>"
                                class="btn-studio"
                            >
                                <i class="fas fa-palette"></i>
                                Design Studio
                            </a>

                            <a
                                href="edit.php?id=<?= $project_id; ?>"
                                class="btn-edit"
                                title="Edit proyek"
                            >
                                <i class="fas fa-pen"></i>
                            </a>

                        </div>

                    </div>
                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty-state">
            <i class="fas fa-folder-open"></i>

            <h3>Belum Ada Proyek</h3>

            <p>
                Kamu belum memiliki proyek. Mulai buat proyek pertamamu sekarang.
            </p>

            <a href="tambah.php" class="btn-add d-inline-block mt-2">
                <i class="fas fa-plus"></i> Buat Proyek
            </a>
        </div>

    <?php endif; ?>

</main>

<footer>
    © <?= date('Y'); ?> VISUALOGIC — Design Beyond Pixels.
</footer>

</body>
</html>
