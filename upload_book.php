<?php
include 'config.php';

// Require a database-verified admin account.
$admin_id = (int)($_SESSION['admin_id'] ?? 0);
if ($admin_id < 1) { header('Location: admin_login.php'); exit(); }
$admin_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE id = ? AND is_admin = 1 LIMIT 1");
if (!$admin_stmt) { http_response_code(500); exit('Unable to verify admin access.'); }
mysqli_stmt_bind_param($admin_stmt, 'i', $admin_id);
mysqli_stmt_execute($admin_stmt);
$admin_result = mysqli_stmt_get_result($admin_stmt);
$is_admin = $admin_result && mysqli_num_rows($admin_result) === 1;
mysqli_stmt_close($admin_stmt);
if (!$is_admin) { http_response_code(403); exit('Admin access required.'); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed.'); }
if (empty($_SESSION['admin_csrf']) || !hash_equals($_SESSION['admin_csrf'], (string)($_POST['csrf_token'] ?? ''))) {
    http_response_code(403); exit('Security check failed. Refresh the admin page and try again.');
}

$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
if ($title === '' || $author === '' || strlen($title) > 255 || strlen($author) > 255 || strlen($category) > 120 || strlen($description) > 10000) {
    http_response_code(400); exit('Please provide valid book details.');
}
if (!isset($_FILES['pdf'], $_FILES['cover'])) { http_response_code(400); exit('Both PDF and cover image are required.'); }

$pdf = $_FILES['pdf'];
$cover = $_FILES['cover'];
if ($pdf['error'] !== UPLOAD_ERR_OK || $cover['error'] !== UPLOAD_ERR_OK ||
    !is_uploaded_file($pdf['tmp_name']) || !is_uploaded_file($cover['tmp_name']) ||
    $pdf['size'] < 8 || $pdf['size'] > 20 * 1024 * 1024 ||
    $cover['size'] < 1 || $cover['size'] > 5 * 1024 * 1024) {
    http_response_code(400); exit('Upload failed or file size is outside the allowed limit.');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$pdf_mime = $finfo->file($pdf['tmp_name']);
$pdf_handle = fopen($pdf['tmp_name'], 'rb');
$pdf_signature = $pdf_handle ? fread($pdf_handle, 5) : '';
if ($pdf_handle) fclose($pdf_handle);
if ($pdf_mime !== 'application/pdf' || $pdf_signature !== '%PDF-') {
    http_response_code(400); exit('The uploaded document is not a valid PDF.');
}

$cover_mime = $finfo->file($cover['tmp_name']);
$cover_types = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp'];
$image_info = @getimagesize($cover['tmp_name']);
if (!isset($cover_types[$cover_mime]) || !$image_info || $image_info[0] > 6000 || $image_info[1] > 6000) {
    http_response_code(400); exit('Cover must be a valid JPG, PNG or WEBP image within dimension limits.');
}

$pdf_dir = __DIR__ . '/uploads/pdfs';
$cover_dir = __DIR__ . '/uploads/covers';
foreach ([$pdf_dir, $cover_dir] as $dir) {
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        http_response_code(500); exit('Cannot create upload directory.');
    }
}
$pdf_name = bin2hex(random_bytes(16)) . '.pdf';
$cover_name = bin2hex(random_bytes(16)) . '.' . $cover_types[$cover_mime];
if (!move_uploaded_file($pdf['tmp_name'], $pdf_dir . '/' . $pdf_name)) {
    http_response_code(500); exit('Could not save PDF.');
}
if (!move_uploaded_file($cover['tmp_name'], $cover_dir . '/' . $cover_name)) {
    @unlink($pdf_dir . '/' . $pdf_name);
    http_response_code(500); exit('Could not save cover image.');
}

$pdf_path = 'uploads/pdfs/' . $pdf_name;
$cover_path = 'uploads/covers/' . $cover_name;
// Use only columns present in the project's base books schema.
$stmt = mysqli_prepare($conn, "INSERT INTO books (title,author,description,cover_image_url,read_link,download_epub_link,source) VALUES (?,?,?,?,?,?, 'PDF Upload')");
if (!$stmt) {
    @unlink($pdf_dir . '/' . $pdf_name); @unlink($cover_dir . '/' . $cover_name);
    http_response_code(500); exit('Could not prepare book details.');
}
mysqli_stmt_bind_param($stmt, 'ssssss', $title, $author, $description, $cover_path, $pdf_path, $pdf_path);
$ok = mysqli_stmt_execute($stmt);
$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);
if (!$ok) {
    @unlink($pdf_dir . '/' . $pdf_name); @unlink($cover_dir . '/' . $cover_name);
    error_log('Book upload failed: ' . $error);
    http_response_code(500); exit('Could not save book details.');
}
$_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
header('Location: admin_dashboard.php?page=all_books&uploaded=1');
exit();
?>