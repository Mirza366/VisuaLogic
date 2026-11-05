
<?php

// Membuat token keamanan CSRF
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Memeriksa token keamanan pada form
function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            http_response_code(419);
            exit("Permintaan tidak valid. Silakan muat ulang halaman.");
        }
    }
}

// Mengamankan output HTML
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Memeriksa status login
function require_login() {
    if (empty($_SESSION['login'])) {
        header("Location: ../auth/login.php");
        exit;
    }
}

?>
