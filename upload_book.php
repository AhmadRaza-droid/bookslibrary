<?php
include 'config.php';
if (!isset($_SESSION['admin'])) { header('Location: admin_login.php'); exit(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($title === '' || $author === '' || empty($_FILES['pdf']) || empty($_FILES['cover'])) exit('Missing required fields.');
    $pdf = $_FILES['pdf']; $cover = $_FILES['cover'];
    if ($pdf['error'] !== UPLOAD_ERR_OK || $pdf['size'] > 20*1024*1024 || $cover['error'] !== UPLOAD_ERR_OK || $cover['size'] > 5*1024*1024) exit('Upload failed or file too large.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if ($finfo->file($pdf['tmp_name']) !== 'application/pdf') exit('Only PDF files are allowed.');
    $mime = $finfo->file($cover['tmp_name']);
    $extensions = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($extensions[$mime])) exit('Cover must be JPG, PNG or WEBP.');
    $pdfDir = __DIR__ . '/uploads/pdfs'; $coverDir = __DIR__ . '/uploads/covers';
    foreach ([$pdfDir,$coverDir] as $dir) if (!is_dir($dir) && !mkdir($dir,0755,true) && !is_dir($dir)) exit('Cannot create upload directory.');
    $pdfName = bin2hex(random_bytes(16)).'.pdf';
    $coverName = bin2hex(random_bytes(16)).'.'.$extensions[$mime];
    if (!move_uploaded_file($pdf['tmp_name'],$pdfDir.'/'.$pdfName)) exit('Could not save PDF.');
    if (!move_uploaded_file($cover['tmp_name'],$coverDir.'/'.$coverName)) { @unlink($pdfDir.'/'.$pdfName); exit('Could not save cover.'); }
    $pdfPath='uploads/pdfs/'.$pdfName; $coverPath='uploads/covers/'.$coverName;
    $stmt=$conn->prepare("INSERT INTO books (title,author,category,description,cover_image_url,read_link,download_epub_link,pdf_file,cover_file,source) VALUES (?,?,?,?,?,?,?,?,?,'PDF Upload')");
    $stmt->bind_param('sssssssss',$title,$author,$category,$description,$coverPath,$pdfPath,$pdfPath,$pdfPath,$coverPath);
    if ($stmt->execute()) { echo "<script>alert('Book uploaded successfully.');window.location.href='admin_dashboard.php';</script>"; exit(); }
    @unlink($pdfDir.'/'.$pdfName); @unlink($coverDir.'/'.$coverName);
    error_log('Book upload failed: '.$stmt->error); exit('Could not save book details.');
}
?>