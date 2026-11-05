
<?php
session_start();
require_once "../config/koneksi.php";
require_once "../config/functions.php";

require_login();
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id_user = (int) $_SESSION['id'];
$id_project = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id_project || $id_project < 1) {
    header("Location: index.php");
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM projects WHERE id = ? AND user_id = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $id_project, $id_user);
mysqli_stmt_execute($stmt);

$deleted = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

if ($deleted > 0) {
    header("Location: index.php?pesan=dihapus");
} else {
    header("Location: index.php");
}

exit;
?>
