
<?php
session_start();

require_once "../config/koneksi.php";
require_once "../config/functions.php";

header("Content-Type: application/json; charset=utf-8");

function json_response($success, $message, $extra = [], $status = 200) {
    http_response_code($status);

    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $extra));

    exit;
}

if (empty($_SESSION['id'])) {
    json_response(false, "Silakan login kembali.", [], 401);
}

$user_id = (int) $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, "Metode request tidak diizinkan.", [], 405);
}

// Validasi CSRF
$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    json_response(false, "Token keamanan tidak valid. Muat ulang halaman.", [], 419);
}

$project_id = isset($_POST['project_id']) ? (int) $_POST['project_id'] : 0;

if ($project_id <= 0) {
    json_response(false, "Project tidak valid.", [], 400);
}

// Pastikan project milik pengguna
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id
     FROM projects
     WHERE id = ? AND user_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "ii", $project_id, $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$project = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$project) {
    json_response(false, "Project tidak ditemukan.", [], 404);
}

// Validasi file
if (!isset($_FILES['image'])) {
    json_response(false, "Silakan pilih gambar terlebih dahulu.", [], 400);
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    json_response(false, "Upload gambar gagal. Kode: " . $file['error'], [], 400);
}

if ($file['size'] > 5 * 1024 * 1024) {
    json_response(false, "Ukuran gambar maksimal 5 MB.", [], 400);
}

if ($file['size'] <= 0) {
    json_response(false, "File gambar kosong.", [], 400);
}

// Periksa MIME berdasarkan isi file
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);

$allowed = [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
    "image/webp" => "webp"
];

if (!isset($allowed[$mime])) {
    json_response(false, "Format gambar tidak didukung.", [], 400);
}

// Pastikan file benar-benar gambar
$image_info = @getimagesize($file['tmp_name']);

if ($image_info === false) {
    json_response(false, "File bukan gambar yang valid.", [], 400);
}

// Folder penyimpanan
$upload_dir = __DIR__ . "/../uploads/design/";

if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        json_response(false, "Folder upload tidak dapat dibuat.", [], 500);
    }
}

if (!is_writable($upload_dir)) {
    json_response(false, "Folder upload tidak memiliki izin tulis.", [], 500);
}

// Nama file acak
$extension = $allowed[$mime];
$new_name = bin2hex(random_bytes(16)) . "." . $extension;

$destination = $upload_dir . $new_name;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    json_response(false, "Gagal menyimpan file gambar.", [], 500);
}

// Simpan informasi file ke database
$original_name = basename($file['name']);
$relative_path = "../uploads/design/" . $new_name;
$file_size = (int) $file['size'];

$stmt = mysqli_prepare(
    $koneksi,
    "INSERT INTO assets
     (project_id, nama_file, lokasi_file, tipe_file, ukuran_file)
     VALUES (?, ?, ?, ?, ?)"
);

if (!$stmt) {
    unlink($destination);
    json_response(false, "Gagal menyiapkan penyimpanan database.", [], 500);
}

mysqli_stmt_bind_param(
    $stmt,
    "isssi",
    $project_id,
    $original_name,
    $relative_path,
    $mime,
    $file_size
);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    unlink($destination);

    json_response(false, "Gagal menyimpan informasi gambar.", [], 500);
}

mysqli_stmt_close($stmt);

json_response(
    true,
    "Gambar berhasil diunggah.",
    [
        "image_url" => $relative_path
    ]
);
