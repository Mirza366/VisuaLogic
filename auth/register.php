
<?php
session_start();

require_once "../config/koneksi.php";
require_once "../config/functions.php";

if (!empty($_SESSION['login'])) {
    header("Location: ../dashboard/index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if (
        $nama === '' ||
        $username === '' ||
        $email === '' ||
        $password === '' ||
        $konfirmasi === ''
    ) {
        $error = "Semua kolom wajib diisi.";
    } elseif (
        !preg_match('/^[\p{L}\s]+$/u', $nama)
    ) {
        $error = "Nama hanya boleh berisi huruf dan spasi.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{4,50}$/', $username)) {
        $error = "Username minimal 4 karakter dan hanya boleh menggunakan huruf, angka, atau garis bawah.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (strlen($password) < 8) {
        $error = "Password minimal 8 karakter.";
    } elseif ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak sama.";
    } else {

        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($cek, "ss", $username, $email);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "Username atau email sudah digunakan.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $query = mysqli_prepare(
                $koneksi,
                "INSERT INTO users (nama, username, email, password)
                 VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $query,
                "ssss",
                $nama,
                $username,
                $email,
                $password_hash
            );

            if (mysqli_stmt_execute($query)) {
                mysqli_stmt_close($query);
                mysqli_stmt_close($cek);

                header("Location: login.php?registered=1");
                exit;
            } else {
                $error = "Pendaftaran gagal. Silakan coba kembali.";
            }

            mysqli_stmt_close($query);
        }

        mysqli_stmt_close($cek);
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - VISUALOGIC</title>

    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<div class="auth-wrapper">

    <div class="auth-left">

        <a href="../index.php" class="auth-brand">
            <i class="fa-solid fa-layer-group"></i>
            VISUAL<span>OGIC</span>
        </a>

        <div class="auth-message">
            <small>JOIN OUR CREATIVE COMMUNITY</small>

            <h1>
                Your Ideas.<br>
                Your Vision.<br>
                <span>Your Future.</span>
            </h1>

            <p>
                Buat akun VISUALOGIC dan mulai perjalanan kreatif
                kamu dalam dunia desain, coding, dan teknologi.
            </p>
        </div>

        <div class="auth-footer">
            © <?php echo date("Y"); ?> VISUALOGIC. All Rights Reserved.
        </div>

    </div>

    <div class="auth-right">

        <div class="auth-card">

            <h2>Create Account</h2>

            <p class="auth-subtitle">
                Daftarkan akun kamu untuk mulai menggunakan VISUALOGIC.
            </p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error">
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo e(csrf_token()); ?>"
                >

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input
                        type="text"
                        name="nama"
                        placeholder="Masukkan nama lengkap"
                        value="<?php echo e($_POST['nama'] ?? ''); ?>"
                        maxlength="100"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input
                        type="text"
                        name="username"
                        placeholder="Buat username"
                        value="<?php echo e($_POST['username'] ?? ''); ?>"
                        maxlength="50"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input
                        type="email"
                        name="email"
                        placeholder="nama@email.com"
                        value="<?php echo e($_POST['email'] ?? ''); ?>"
                        maxlength="100"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input
                        type="password"
                        name="password"
                        placeholder="Minimal 8 karakter"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input
                        type="password"
                        name="konfirmasi"
                        placeholder="Ulangi password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="btn-auth">
                    Create Account <i class="fa-solid fa-arrow-right"></i>
                </button>

            </form>

            <div class="auth-switch">
                Sudah mempunyai akun?
                <a href="login.php">Login sekarang</a>
            </div>

        </div>

    </div>

</div>

</body>
</html>
