
<?php
session_start();
require_once "../config/koneksi.php";
require_once "../config/functions.php";

require_login();

$id_user = (int) $_SESSION['id'];
$id_project = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_project || $id_project < 1) {
    header("Location: index.php");
    exit;
}

/* Ambil proyek milik pengguna yang sedang login */
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama_project, deskripsi, jenis, status
     FROM projects
     WHERE id = ? AND user_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "ii", $id_project, $id_user);
mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $nama_project,
    $deskripsi,
    $jenis,
    $status
);

$project_found = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$project_found) {
    http_response_code(404);
    exit("Proyek tidak ditemukan atau Anda tidak memiliki akses.");
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $nama_project = trim($_POST['nama_project'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $jenis = $_POST['jenis'] ?? '';
    $status = $_POST['status'] ?? '';

    $jenis_valid = ['design', 'code', 'hybrid'];
    $status_valid = ['draft', 'published'];

    if ($nama_project === '') {
        $errors[] = "Nama proyek wajib diisi.";
    } elseif (mb_strlen($nama_project) > 150) {
        $errors[] = "Nama proyek maksimal 150 karakter.";
    }

    if (!in_array($jenis, $jenis_valid, true)) {
        $errors[] = "Jenis proyek tidak valid.";
    }

    if (!in_array($status, $status_valid, true)) {
        $errors[] = "Status proyek tidak valid.";
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE projects
             SET nama_project = ?, deskripsi = ?, jenis = ?, status = ?
             WHERE id = ? AND user_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssssii",
            $nama_project,
            $deskripsi,
            $jenis,
            $status,
            $id_project,
            $id_user
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: index.php?pesan=diubah");
            exit;
        }

        $errors[] = "Proyek gagal diperbarui. Silakan coba lagi.";
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Proyek | VISUALOGIC</title>
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
            <i class="fa-solid fa-pen-to-square"></i>
        </div>
        <h1>Edit Proyek</h1>
        <p>Perbarui informasi proyek VISUALOGIC Anda.</p>
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
                   value="<?= e($nama_project); ?>">
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi Proyek</label>
            <textarea id="deskripsi" name="deskripsi" rows="5"><?= e($deskripsi); ?></textarea>
        </div>

        <div class="form-group">
            <label for="jenis">Jenis Proyek</label>
            <select name="jenis" id="jenis" required>
                <option value="design" <?= $jenis === 'design' ? 'selected' : ''; ?>>
                    Design
                </option>
                <option value="code" <?= $jenis === 'code' ? 'selected' : ''; ?>>
                    Code
                </option>
                <option value="hybrid" <?= $jenis === 'hybrid' ? 'selected' : ''; ?>>
                    Hybrid
                </option>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status Proyek</label>
            <select name="status" id="status" required>
                <option value="draft" <?= $status === 'draft' ? 'selected' : ''; ?>>
                    Draft
                </option>
                <option value="published" <?= $status === 'published' ? 'selected' : ''; ?>>
                    Published
                </option>
            </select>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
        </div>

    </form>

</div>

</body>
</html>
