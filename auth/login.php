
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

    $identitas = trim($_POST['identitas'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identitas === '' || $password === '') {
        $error = "Username/email dan password wajib diisi.";
    } else {

        $query = mysqli_prepare(
            $koneksi,
            "SELECT id, nama, username, email, password
             FROM users
             WHERE username = ? OR email = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($query, "ss", $identitas, $identitas);
        mysqli_stmt_execute($query);

        mysqli_stmt_bind_result(
            $query,
            $id,
            $nama,
            $username,
            $email,
            $password_hash
        );

        if (
            mysqli_stmt_fetch($query) &&
            password_verify($password, $password_hash)
        ) {
            mysqli_stmt_close($query);

            session_regenerate_id(true);

            $_SESSION['login'] = true;
            $_SESSION['id'] = $id;
            $_SESSION['nama'] = $nama;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;

            header("Location: ../dashboard/index.php");
            exit;
        } else {
            $error = "Username/email atau password salah.";
        }

        mysqli_stmt_close($query);
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VISUALOGIC</title>

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

            <small>WELCOME BACK</small>

            <h1>
                Create More.<br>
                Explore More.<br>
                <span>Be Creative.</span>
            </h1>

            <p>
                Masuk ke ruang kerja VISUALOGIC dan lanjutkan
                semua proyek kreatif kamu.
            </p>

        </div>

        <div class="auth-footer">
            © <?php echo date("Y"); ?> VISUALOGIC. All Rights Reserved.
        </div>

    </div>

    <div class="auth-right">

        <div class="auth-card">

            <h2>Welcome Back!</h2>

            <p class="auth-subtitle">
                Silakan login untuk masuk ke akun VISUALOGIC kamu.
            </p>

            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">
                    Pendaftaran berhasil! Silakan login menggunakan akun kamu.
                </div>
            <?php endif; ?>

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
                    <label>Username atau Email</label>
                    <input
                        type="text"
                        name="identitas"
                        placeholder="Masukkan username atau email"
                        value="<?php echo e($_POST['identitas'] ?? ''); ?>"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input
                        type="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn-auth">
                    Login to Account <i class="fa-solid fa-arrow-right"></i>
                </button>

            </form>

            <div class="auth-switch">
                Belum mempunyai akun?
                <a href="register.php">Daftar sekarang</a>
            </div>

            <div class="auth-switch">
                <a href="../index.php">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Home
                </a>
            </div>

        </div>

    </div>

</div>

</body>
</html>
