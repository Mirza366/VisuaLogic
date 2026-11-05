
<?php
session_start();
require_once "../config/koneksi.php";
require_once "../config/functions.php";

require_login();

$errors = [];
$nama_project = '';
$deskripsi = '';
$jenis = 'design';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $nama_project = trim($_POST['nama_project'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $jenis = $_POST['jenis'] ?? 'design';

    $jenis_valid = ['design', 'code', 'hybrid'];

    if ($nama_project === '') {
        $errors[] = "Nama proyek wajib diisi.";
    } elseif (mb_strlen($nama_project) > 150) {
        $errors[] = "Nama proyek maksimal 150 karakter.";
    }

    if (!in_array($jenis, $jenis_valid, true)) {
        $errors[] = "Jenis proyek tidak valid.";
    }

    if (empty($errors)) {
        $id_user = (int) $_SESSION['id'];

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO projects (user_id, nama_project, deskripsi, jenis, status)
             VALUES (?, ?, ?, ?, 'draft')"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "isss",
            $id_user,
            $nama_project,
            $deskripsi,
            $jenis
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: index.php?pesan=ditambah");
            exit;
        }

        $errors[] = "Proyek gagal disimpan. Silakan coba lagi.";
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Proyek | VISUALOGIC</title>
    <link rel="stylesheet" href="../assets/css/projects.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<div class="form-container">

    <a href="index.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke My Projects
    </a>

    <div class="form-heading">
        <div class="form-heading-icon">
            <i class="fa-solid fa-folder-plus"></i>
        </div>
        <h1>Buat Proyek Baru</h1>
        <p>Masukkan informasi dasar proyek yang ingin Anda buat.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="project-form">

        <input type="hidden" name="csrf_token"
               value="<?= e(csrf_token()); ?>">

        <div class="form-group">
            <label for="nama_project">Nama Proyek</label>
            <input type="text" id="nama_project" name="nama_project"
                   maxlength="150" required
                   placeholder="Contoh: Website Portofolio"
                   value="<?= e($nama_project); ?>">
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi Proyek</label>
            <textarea id="deskripsi" name="deskripsi" rows="5"
                      placeholder="Ceritakan secara singkat tentang proyek ini..."><?= e($deskripsi); ?></textarea>
        </div>

        <div class="form-group">
            <label for="jenis">Jenis Proyek</label>
            <select name="jenis" id="jenis" required>
                <option value="design" <?= $jenis === 'design' ? 'selected' : ''; ?>>
                    Design - Proyek desain visual
                </option>
                <option value="code" <?= $jenis === 'code' ? 'selected' : ''; ?>>
                    Code - Proyek pemrograman
                </option>
                <option value="hybrid" <?= $jenis === 'hybrid' ? 'selected' : ''; ?>>
                    Hybrid - Gabungan desain dan kode
                </option>
            </select>
        </div>

        <div class="form-note">
            <i class="fa-solid fa-circle-info"></i>
            Proyek baru akan disimpan dengan status Draft.
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Proyek
            </button>
        </div>

    </form>

</div>

</body>
</html>
